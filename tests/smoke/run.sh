#!/usr/bin/env bash
#------------------------------------------------------------------------------
# The smoke test: a real Drupal, in Docker, with the module from the working
# tree. It installs Drupal with Drush on SQLite, enables the module, configures
# it, submits its settings form, places its block, and checks the rendered
# pages with curl. It never contacts a Smarter platform.
# See skills/smarter-drupal-testing.
#
#   tests/smoke/run.sh                                  # drupal:11, port 8091
#   DRUPAL_IMAGE=drupal:10.3 SMOKE_PORT=8092 tests/smoke/run.sh
#------------------------------------------------------------------------------
set -euo pipefail
cd "$(dirname "$0")/../.."
IMAGE=${DRUPAL_IMAGE:-drupal:11}
PORT=${SMOKE_PORT:-8091}
URL="http://localhost:$PORT"
NAME="smarter-drupal-smoke-$PORT"
JAR=$(mktemp)
FAILURES=0

cleanup() { docker rm -f "$NAME" >/dev/null 2>&1 || true; rm -f "$JAR"; }
trap cleanup EXIT

d() { docker exec -u www-data -w /opt/drupal "$NAME" "$@"; }
drush() { d vendor/bin/drush "$@"; }
check() { # check <description> <html> <grep -E pattern> [absent]
  if grep -Eq "$3" <<<"$2"; then found=1; else found=0; fi
  if [[ "${4:-}" == "absent" ]]; then found=$((1 - found)); fi
  if [[ $found -eq 1 ]]; then echo "ok:   $1"; else
    echo "FAIL: $1"; FAILURES=$((FAILURES + 1))
    grep -Eo '<script[^>]*(smarter|ui-chat)[^>]*>|<smarter-chat[^>]*>|"smarterChat":\{[^}]*\}' <<<"$2" | sed 's/^/      /' || true
  fi
}

docker run -d --name "$NAME" -p "$PORT:80" -v "$PWD:/opt/drupal/web/modules/contrib/smarter_chat:ro" "$IMAGE" >/dev/null
# everything but the module, which is mounted read-only.
docker exec "$NAME" bash -c 'mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer &&
  find /opt/drupal -path /opt/drupal/web/modules/contrib/smarter_chat -prune -o -exec chown www-data:www-data {} +'
echo "Adding Drush"
d composer require drush/drush --no-interaction --quiet
drush site:install standard --db-url=sqlite://sites/default/files/.ht.sqlite --account-pass=admin --site-name=Smoke -y -q
drush pm:install smarter_chat -y -q
drush config:set smarter_chat.settings chatbot https://chat.example.com/ -y -q
drush config:set smarter_chat.settings label 'Ask <b>us</b>' -y -q
drush config:set smarter_chat.settings position bottom-left -y -q
drush php:eval "\Drupal\block\Entity\Block::create(['id' => 'smarter_chat_smoke', 'theme' => \Drupal::config('system.theme')->get('default'), 'region' => 'content', 'weight' => 100, 'plugin' => 'smarter_chat_block', 'settings' => ['id' => 'smarter_chat_block', 'label' => 'Smarter Chat', 'label_display' => '0', 'provider' => 'smarter_chat', 'chatbot' => 'https://block.example.com/', 'height' => '70vh', 'width' => '100%']])->save();"
echo "Drupal $(drush status --field=drupal-version)"

HOME_HTML=$(curl -s "$URL/")
check "the launcher's options are in drupalSettings" "$HOME_HTML" '"smarterChat":\{"chatbot":"https:\\/\\/chat\.example\.com\\/","mode":"launcher","position":"bottom-left"'
check "the label is sanitized" "$HOME_HTML" '"label":"Ask us"'
check "the embed script is loaded from jsDelivr, async" "$HOME_HTML" '<script src="https://cdn\.jsdelivr\.net/npm/@smarter\.sh/ui-chat@[0-9.]+/dist/embed\.js" async'
# the standard profile aggregates javascript, which hides the config script's name: compare without aggregation.
drush config:set system.performance js.preprocess 0 -y -q && drush cache:rebuild -q
ORDER=$(curl -s "$URL/" | grep -Eo 'smarter-chat\.config\.js|ui-chat@[0-9.]+/dist/embed\.js' | tr '\n' ' ')
echo "      order: $ORDER"; check "the config script comes before the embed script" "$ORDER" '^smarter-chat\.config\.js ui-chat'
drush config:set system.performance js.preprocess 1 -y -q && drush cache:rebuild -q
check "the block renders the element" "$HOME_HTML" '<smarter-chat chatbot="https://block\.example\.com/" height="70vh" width="100%"></smarter-chat>'

# log in as the admin, with a one-time login link.
curl -s -c "$JAR" -b "$JAR" -o /dev/null -L "$(drush user:login --uri="$URL" --no-browser)"
ADMIN_HTML=$(curl -s -b "$JAR" "$URL/admin/config/services/smarter-chat")
check "the settings form is there for the admin" "$ADMIN_HTML" 'name="chatbot"'
check "the settings form shows the origin to allow" "$ADMIN_HTML" "<code>$URL</code>"
check "no launcher on admin pages" "$ADMIN_HTML" '"smarterChat"' absent

# submit the form: an invalid chatbot is rejected, a valid one with a self-hosted script is saved.
submit() { # submit <chatbot> <script_url> <launcher 0|1>
  local form build token
  form=$(curl -s -b "$JAR" "$URL/admin/config/services/smarter-chat")
  build=$(grep -Eo 'name="form_build_id" value="[^"]+"' <<<"$form" | head -1 | sed 's/.*value="//; s/"$//')
  token=$(grep -Eo 'name="form_token" value="[^"]+"' <<<"$form" | head -1 | sed 's/.*value="//; s/"$//')
  local launcher=()
  [[ "$3" == "1" ]] && launcher=(--data-urlencode "launcher=1")
  curl -s -b "$JAR" -c "$JAR" -L "$URL/admin/config/services/smarter-chat" \
    --data-urlencode "chatbot=$1" --data-urlencode "script_url=$2" ${launcher[@]+"${launcher[@]}"} \
    --data-urlencode "api_key=" --data-urlencode "position=bottom-right" --data-urlencode "label=Help" \
    --data-urlencode "color=#123456" --data-urlencode "form_build_id=$build" --data-urlencode "form_token=$token" \
    --data-urlencode "form_id=smarter_chat_settings" --data-urlencode "op=Save configuration"
}
check "the form rejects a javascript: url" "$(submit 'javascript:alert(1)' '' 1)" 'error'
check "the form saves valid settings" "$(submit 'https://saved.example.com/' 'https://www.example.com/libraries/smarter-chat/embed.js' 1)" 'The configuration options have been saved'
HOME_HTML=$(curl -s "$URL/")
check "saved settings show on the next request (cache tags)" "$HOME_HTML" '"chatbot":"https:\\/\\/saved\.example\.com\\/"'
check "the self-hosted script replaces jsDelivr's (library_info invalidated)" "$HOME_HTML" '<script src="https://www\.example\.com/libraries/smarter-chat/embed\.js" async'
submit 'https://saved.example.com/' '' 0 >/dev/null
HOME_HTML=$(curl -s "$URL/")
check "without the launcher, no drupalSettings.smarterChat" "$HOME_HTML" '"smarterChat"' absent
check "without the launcher, the block still loads the script" "$HOME_HTML" 'ui-chat@[0-9.]+/dist/embed\.js'
check "the block uses its own chatbot" "$HOME_HTML" '<smarter-chat chatbot="https://block\.example\.com/"'

if [[ $FAILURES -gt 0 ]]; then echo "$FAILURES check(s) failed"; exit 1; fi
echo "all checks passed"
