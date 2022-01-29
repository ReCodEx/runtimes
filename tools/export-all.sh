#!/bin/bash

TARGET_DIR=$1
RUNTIMES='bash c-gcc-linux cs-dotnet-core cxx-gcc-linux data-linux freepascal-linux go groovy haskell java kotlin node-linux php-linux prolog python3 rust scala'
EXEC='/opt/recodex/core-api/bin/console'
DATE=`date '+%Y-%m-%d'`

if [ ! -d "$TARGET_DIR" ]; then
	echo "Target directory $TARGET_DIR does not exist."
	exit 1
fi
cd "$TARGET_DIR" || exit 1

for RUNTIME in $RUNTIMES; do
	echo -n "Exporting $RUNTIME ... "
	TMP="/tmp/recodex-$RUNTIME.zip"
	
	$EXEC runtimes:export $RUNTIME "$TMP"
	if [[ $? != 0 ]]; then
		echo "export FAILED."
		exit 2
	fi
	
	LAST_EXISTING=`ls -1 | grep "$RUNTIME" | sort | tail -1`
	DIFF_RES=1
	if [ ! -z "$LAST_EXISTING" ]; then
		# compare the export with last existing version
		diff-pkgs.php "$TMP" "$LAST_EXISTING"
		DIFF_RES=$?
	fi
	
	if [[ $DIFF_RES != 0 ]]; then
		# no match with previous export
		TARGET_FILE="./$RUNTIME-$DATE.zip"
		if [ -f "$TARGET_FILE" ]; then
			echo "file $TARGET_FILE already exists. If you need to perform multiple exports in one day, fix the collisions manually."
			exit 3
		fi
		mv "$TMP" "$TARGET_FILE"
		echo "file $TARGET_FILE created"
	else
		echo "same as last version $LAST_EXISTING"
		rm "$TMP"
	fi
done
