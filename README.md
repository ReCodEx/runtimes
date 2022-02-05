# Runtime Environments for ReCodEx

Runtime environments that can be imported into deployed ReCodEx instance via core-api command.

- [**generic**](https://github.com/ReCodEx/runtimes/tree/main/generic) folder holds packages that might be used by anyone
- [**specialized**](https://github.com/ReCodEx/runtimes/tree/main/specialized) directory contains packages for specific purposes that were designed for our pilot instance at [MFF-UK](https://www.mff.cuni.cz/en)
- [**deprecated**](https://github.com/ReCodEx/runtimes/tree/main/deprecated) directory archives outdated packages

The runtime packages can be imported as follows
```
$> bin/console runtimes:import <pkg-file>
```
where the `bin/console` refers to CLI script of the [core-api](https://github.com/ReCodEx/api) module.

Note, that the package contains
- runtime environment metadata
- all adjacent pipelines and their configurations
- supplementary files for all the pipelines

If some of the entities already exist, they are overwritten. This may be potentially problematic since the pipelines are shared among the runtimes.
Readme in [**generic**](https://github.com/ReCodEx/runtimes/tree/main/generic) folder contains a listing with quick overview of generic packages and their overlaps (which of the other packages share some of the pipelines).

> **Note that the import will not alter existing exercise configurations!**
> 
> If the import creates new pipelines and changes associations with particular runtime environment, existing exercises will not be affected. That might be a problem if you need to start using new pipelines immediately. If the pipelines already exist and are overwritten, there is no problem since their IDs (which are kept in exercise configs) are not changed.

## Package structure

Package is a ZIP archive with the following structure. The most essential part is the `manifest.json` file which holds all the data except for pipeline config structures and supplementary files. The structure of the manifest is

```json
{
    "pkgVersion": 1,
    "runtime": {},
    "pipelines": [ {} ]
}
```

Currently all packages has version `1`, but this field may server for future extensions.

The runtime is a collection with serialized runtime entity. For example, this is the `bash` runtime.

```json
"runtime": {
    "id": "bash",
    "name": "Bash",
    "longName": "Bash (Bourne Again SHell)",
    "extensions": "[sh]",
    "platform": "GNU\/Linux",
    "description": "Shell scripts executed with Bash",
    "defaultVariables": [
        {
            "name": "source-files",
            "type": "file[]",
            "value": "*.sh"
        }
    ]
}
```

The pipelines section is a list of collections, each representing serialized pipeline entity:

```json
"pipelines": [{
    "id": "93de0c1b-5bb3-11ea-9e7a-005056854569",
    "name": "Bash execution & evaluation [stdout]",
    "version": 2,
    "createdAt": 1583063742,
    "updatedAt": 1583063763,
    "description": "Executes Bash shell script ...",
    "supplementaryFiles": [],
    "parameters": {
        "isCompilationPipeline": false,
        "isExecutionPipeline": true,
        "judgeOnlyPipeline": false,
        "producesStdout": true,
        "producesFiles": false,
        "hasEntryPoint": true,
        "hasExtraFiles": false
    }
}]
```

Each pipeline has its structure saved in a separate file in the ZIP archive, name of which is based on pipeline ID (`93de0c1b-5bb3-11ea-9e7a-005056854569.json` for the pipeline in the previous example).

If a pipeline has supplementary files, each file is serialized in a collection:

```json
"supplementaryFiles": [{
    "name": "Wrapper.cs",
    "uploadedAt": 1598524601,
    "size": 3939,
    "hash": "abbcfc9ffefa2438e28857082171b2745af7e740"
}]
```

The actual files are stored in the zip archive under subdirectory, which has the exact name as the pipeline ID and file name corresponds to the `name` property. Properties `size` and `hash` can be computed from the file itself, the values in the manifest are simply for convenience, so anyone can easily find out, which files have been modified when comparing two versions of the same package.
