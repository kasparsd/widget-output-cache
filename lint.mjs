// Run the actionlint WASM linter over the GitHub workflow files.
//
// The bundled actionlint snapshot predates the ubuntu-24.04 runner label,
// and its runActionlint() entry point has no config argument, so the
// stale "runner-label" findings for that valid GitHub-hosted label are
// ignored here.
const IGNORED_KINDS = new Set( [ 'runner-label' ] );
import { createLinter } from 'actionlint';
import { readFile, readdir } from 'node:fs/promises';
import path from 'node:path';

const workflowDir = path.join( '.github', 'workflows' );
const files = ( await readdir( workflowDir ) )
	.filter( ( file ) => /\.(?:ya?ml)$/.test( file ) )
	.map( ( file ) => path.join( workflowDir, file ) )
	.sort();

const linter = await createLinter();
let findings = 0;

for ( const file of files ) {
	const source = await readFile( file, 'utf8' );

	for ( const result of await linter( source, file ) ) {
		if ( IGNORED_KINDS.has( result.kind ) ) {
			continue;
		}
		findings += 1;
		console.log( `${ result.file }:${ result.line }:${ result.column } ${ result.message } [${ result.kind }]` );
	}
}

if ( findings ) {
	process.exitCode = 1;
} else {
	console.log( `No workflow lint problems in ${ files.length } files.` );
}
