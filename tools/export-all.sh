#!/bin/bash

TARGET_DIR=$1
RUNTIMES='bash c-gcc-linux cs-dotnet-core cxx-gcc-linux data-linux freepascal-linux go groovy haskell java java-maven kotlin node-linux php-linux prolog python3 rust scala'
EXEC='/opt/recodex-core/bin/console'
DATE=`date '+%Y-%m-%d'`

cd `dirname "$0"` || exit 1

if [ ! -d "$TARGET_DIR" ]; then
	echo "Target directory $TARGET_DIR does not exist."
	exit 1
fi

for RUNTIME in $RUNTIMES; do
	echo -n "Exporting $RUNTIME ... "
	TMP="/tmp/recodex-$RUNTIME.zip"
	
	$EXEC runtimes:export $RUNTIME "$TMP"
	if [[ $? != 0 ]]; then
		echo "export FAILED."
		exit 2
	fi
	
	LAST_EXISTING=`ls -1 "$TARGET_DIR" | grep "$RUNTIME" | sort | tail -1`
	DIFF_RES=1
	if [ ! -z "$LAST_EXISTING" ]; then
		# compare the export with last existing version
		./diff-pkgs.php "$TMP" "$TARGET_DIR/$LAST_EXISTING"
		DIFF_RES=$?
	fi
	
	if [[ $DIFF_RES != 0 ]]; then
		# no match with previous export
		TARGET_FILE="$TARGET_DIR/$RUNTIME-$DATE.zip"
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
