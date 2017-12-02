#!/bin/sh
# Author:       Valentin Popov
# Email:        info@valentineus.link
# Date:         2017-12-02
# Usage:        /bin/sh build.sh
# Description:  Build the final package for installation in Moodle.

# Updating the Environment
PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
export PATH="$PATH:/usr/local/scripts"

# Build the package
cd ..
mv "./moodle-tool_managertokens" "./tool_managertokens"
zip -9 -r "tool_managertokens.zip" "tool_managertokens" \
        -x "tool_managertokens/.git*"                   \
        -x "tool_managertokens/.travis.yml"             \
        -x "tool_managertokens/build.sh"

# End of work
exit 0