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
	// The markdown readme carries the changelog in the GitHub repo and is
	// converted to readme.txt for WordPress.org, so new entries hold plain
	// `= version =` headings inserted after this marker instead of the
	// `== Changelog ==` heading that readme.txt-based repos use. The same
	// marker lives at the top of CHANGELOG.md.
	changelogInsertAfter: '<!-- changelog -->',
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
					// No readme.txt in the repository: the Stable tag lives
					// in the markdown readme, which the tool reads when
					// readme.txt is absent.
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
