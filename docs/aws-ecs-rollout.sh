#!/usr/bin/env bash
set -euo pipefail

REGION="ap-southeast-2"
CLUSTER="laravel-test-deploy"
WEB_SERVICE="laravel-test-app-service-web"
QUEUE_SERVICE="laravel-test-app-service-queue"
SCHED_SERVICE="laravel-test-service-scheduler"
CONTAINER_NAME="laravel-test"
APP_URL="http://laravel-test-alb-1123745009.ap-southeast-2.elb.amazonaws.com"
TG_ARN="arn:aws:elasticloadbalancing:ap-southeast-2:464097498579:targetgroup/laravel-test-web-tg/307c62fae59278cd"

for cmd in aws jq curl; do
  command -v "$cmd" >/dev/null 2>&1 || { echo "Missing command: $cmd"; exit 1; }
done

patch_register() {
  local svc="$1"
  local td src out

  td=$(aws ecs describe-services \
    --cluster "$CLUSTER" \
    --services "$svc" \
    --region "$REGION" \
    --query "services[0].taskDefinition" \
    --output text)

  src=$(mktemp)
  out=$(mktemp)

  aws ecs describe-task-definition \
    --task-definition "$td" \
    --region "$REGION" \
    --query "taskDefinition" \
    --output json > "$src"

  jq --arg cn "$CONTAINER_NAME" --arg appurl "$APP_URL" '
    def setenv(k; v): (map(select(.name != k)) + [{"name": k, "value": v}]);
    del(.taskDefinitionArn, .revision, .status, .requiresAttributes, .compatibilities, .registeredAt, .registeredBy, .deregisteredAt)
    | .containerDefinitions |= map(
        if .name == $cn then
          .environment = ((.environment // [])
            | setenv("APP_ENV"; "production")
            | setenv("APP_DEBUG"; "false")
            | setenv("APP_URL"; $appurl)
            | setenv("L5_SWAGGER_CONST_HOST"; $appurl)
            | setenv("SESSION_DRIVER"; "file")
            | setenv("CACHE_STORE"; "file")
          )
        else
          .
        end
      )
  ' "$src" > "$out"

  aws ecs register-task-definition \
    --region "$REGION" \
    --cli-input-json "file://$out" \
    --query "taskDefinition.taskDefinitionArn" \
    --output text

  rm -f "$src" "$out"
}

deploy_wait() {
  local svc="$1"
  local td="$2"

  aws ecs update-service \
    --cluster "$CLUSTER" \
    --service "$svc" \
    --task-definition "$td" \
    --force-new-deployment \
    --region "$REGION" >/dev/null

  aws ecs wait services-stable \
    --cluster "$CLUSTER" \
    --services "$svc" \
    --region "$REGION"
}

run_migrate() {
  local net subnets sgs pubip webtd task code

  net=$(mktemp)
  aws ecs describe-services \
    --cluster "$CLUSTER" \
    --services "$WEB_SERVICE" \
    --region "$REGION" \
    --query "services[0].networkConfiguration.awsvpcConfiguration" \
    --output json > "$net"

  subnets=$(jq -r '.subnets | join(",")' "$net")
  sgs=$(jq -r '.securityGroups | join(",")' "$net")
  pubip=$(jq -r '.assignPublicIp' "$net")
  rm -f "$net"

  webtd=$(aws ecs describe-services \
    --cluster "$CLUSTER" \
    --services "$WEB_SERVICE" \
    --region "$REGION" \
    --query "services[0].taskDefinition" \
    --output text)

  task=$(aws ecs run-task \
    --cluster "$CLUSTER" \
    --launch-type FARGATE \
    --task-definition "$webtd" \
    --network-configuration "awsvpcConfiguration={subnets=[$subnets],securityGroups=[$sgs],assignPublicIp=$pubip}" \
    --overrides '{"containerOverrides":[{"name":"laravel-test","command":["php","artisan","migrate","--force"]}]}' \
    --region "$REGION" \
    --query "tasks[0].taskArn" \
    --output text)

  aws ecs wait tasks-stopped \
    --cluster "$CLUSTER" \
    --tasks "$task" \
    --region "$REGION"

  code=$(aws ecs describe-tasks \
    --cluster "$CLUSTER" \
    --tasks "$task" \
    --region "$REGION" \
    --query "tasks[0].containers[0].exitCode" \
    --output text)

  if [[ "$code" != "0" ]]; then
    aws ecs describe-tasks \
      --cluster "$CLUSTER" \
      --tasks "$task" \
      --region "$REGION" \
      --query "tasks[0].[stoppedReason,containers[0].reason]" \
      --output table
    exit 1
  fi
}

echo "[1/5] Rollout WEB"
TD_WEB=$(patch_register "$WEB_SERVICE")
deploy_wait "$WEB_SERVICE" "$TD_WEB"

echo "[2/5] Run migrate --force"
run_migrate

echo "[3/5] Rollout QUEUE"
TD_QUEUE=$(patch_register "$QUEUE_SERVICE")
deploy_wait "$QUEUE_SERVICE" "$TD_QUEUE"

echo "[4/5] Rollout SCHEDULER"
TD_SCHED=$(patch_register "$SCHED_SERVICE")
deploy_wait "$SCHED_SERVICE" "$TD_SCHED"

echo "[5/5] Verify health"
aws elbv2 describe-target-health \
  --target-group-arn "$TG_ARN" \
  --region "$REGION" \
  --query "TargetHealthDescriptions[].{state:TargetHealth.State,reason:TargetHealth.Reason,desc:TargetHealth.Description}" \
  --output table

curl -i --max-time 15 "$APP_URL/up" | head -n 20

echo "DONE"