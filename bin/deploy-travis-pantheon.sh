#!/bin/bash
set -e
# Travis CI Deploy script to Pantheon

# Positional args:
pantheon_site=$1
pantheon_uuid=$2
ssh_identity=$3

cd "$(dirname "$0")/.."
project_dir="$(pwd)"
repo_dir="$HOME/deployment-targets/$pantheon_site"

if [ -z "$TRAVIS_BRANCH" ]; then
    echo "TRAVIS_BRANCH environment variable empty"
    exit 1
fi

ssh-add $ssh_identity

if ! grep -q "codeserver.dev.$pantheon_uuid.drush.in" ~/.ssh/known_hosts; then
    ssh-keyscan -p 2222 codeserver.dev.$pantheon_uuid.drush.in >> ~/.ssh/known_hosts
fi

if ! grep -q "codeserver.dev.$pantheon_uuid.drush.in" ~/.ssh/config; then
    echo "Host $pantheon_site" >> ~/.ssh/config
    echo "  Hostname codeserver.dev.$pantheon_uuid.drush.in" >> ~/.ssh/config
    echo "  User codeserver.dev.$pantheon_uuid" >> ~/.ssh/config
    echo "  IdentityFile $ssh_identity" >> ~/.ssh/config
    echo "  IdentitiesOnly yes" >> ~/.ssh/config
    echo "  Port 2222" >> ~/.ssh/config
    echo "  KbdInteractiveAuthentication no" >> ~/.ssh/config
fi
git config --global user.name "Travis CI"
git config --global user.email "travis-ci+$pantheon_site@xwp.co"

# Set the branch.
if [[ $TRAVIS_BRANCH == 'develop' ]]; then
    pantheon_branch=master
else
    pantheon_branch=$TRAVIS_BRANCH
fi

if [ ! -e "$repo_dir/.git" ]; then
    git clone -v ssh://codeserver.dev.$pantheon_uuid@codeserver.dev.$pantheon_uuid.drush.in:2222/~/repository.git "$repo_dir"
fi

cd "$repo_dir"
git fetch

if git rev-parse --verify --quiet "$pantheon_branch" > /dev/null; then
    git checkout "$pantheon_branch"
else
    git checkout -b "$pantheon_branch"
fi
if git rev-parse --verify --quiet "origin/$pantheon_branch" > /dev/null; then
    git reset --hard "origin/$pantheon_branch"
fi

# Install and build.
cd "$project_dir"
if [ ! -e node_modules/.bin ]; then
    npm install
fi
PATH="node_modules/.bin/:$PATH"
grunt build
version_append=$(git --no-pager log -1 --format="%ad-%h" --date=short)
rsync -avz --delete ./build/ "$repo_dir/wp-content/plugins/amp/"
cat ./build/amp.php |
    sed "/^ \* Version:/ s/$/-$version_append/" |
    sed "/^define( 'AMP__VERSION/ s/' );/-$version_append' );/" > "$repo_dir/wp-content/plugins/amp/amp.php"
git --no-pager log -1 --format="Build AMP plugin at %h: %s" > /tmp/commit-message.txt

# Commit and deploy.
cd "$repo_dir"
git add -A "wp-content/plugins/amp/"
git commit -F /tmp/commit-message.txt
git push origin $pantheon_branch

echo "View site at http://$pantheon_branch-$pantheon_site.pantheonsite.io/"
echo "Access Pantheon dashboard at https://dashboard.pantheon.io/sites/$pantheon_uuid#$pantheon_branch"
