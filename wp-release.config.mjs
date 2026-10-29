import {
	defineConfig,
	wordpressPluginVersion,
	wordpressStableTag,
} from '@wpelevator/wp-release';

// Single-package repository: the plugin lives at the root, so release tags
// are plain versions (the package path placeholder stays empty). Merge a
// release pull request to master and the tag workflow runs the release.
export default defineConfig( {
	baseBranch: 'master',
	readmeChangelog: true,
	// Without an insertion marker, changelog entries in the plugin readme
	// start right after the "== Changelog ==" heading that is already in
	// readme.txt; CHANGELOG.md entries go before the first version heading.
	packages: [
		{
			glob: '.',
			// Identifier from the root manifest name, not from the checkout
			// directory, so the changesets stay valid in any local checkout
			// name and in CI worktrees.
			idFrom: 'package.json#name',
			version: {
				from: wordpressPluginVersion(),
				to: [
					wordpressPluginVersion(),
					wordpressStableTag(),
				],
			},
		},
	],
	// Release integrity defaults for `zip`: an unversioned checksums
	// manifest embedded at the package root of every build. Signing is
	// deferred until the workflow adopts release keys.
	integrity: {
		file: 'checksums.json',
		embed: true,
	},
} );
