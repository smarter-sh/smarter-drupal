// semantic-release versions the module from the conventional commit messages (doc/adr/011-versioning.md), with the
// same rules and release notes as Smarter's other repositories. It runs on each push to main, and to alpha and beta,
// whose releases are prereleases (.github/workflows/pushMain.yml). A release is a tag, which Composer installs, and
// which Drupal.org's packaging reads: smarter_chat.info.yml and composer.json have no version.
export default {
  branches: ["main", { name: "beta", prerelease: true }, { name: "alpha", prerelease: true }],
  plugins: [
    [
      "@semantic-release/commit-analyzer",
      {
        preset: "conventionalcommits",
        releaseRules: [
          { type: "docs", release: false },
          { type: "test", release: false },
          { type: "style", release: false },
          { type: "refactor", release: false },
        ],
        parserOpts: { noteKeywords: ["BREAKING CHANGE", "BREAKING CHANGES"] },
      },
    ],
    [
      "@semantic-release/release-notes-generator",
      {
        preset: "conventionalcommits",
        presetConfig: {
          types: [
            { type: "feat", section: "Features", hidden: false },
            { type: "fix", section: "Bug Fixes", hidden: false },
            { type: "refactor", section: "Refactoring", hidden: false },
            { type: "perf", section: "Performance", hidden: false },
          ],
        },
      },
    ],
    [
      "@semantic-release/changelog",
      {
        changelogFile: "CHANGELOG.md",
        changelogTitle: `# Change Log\n\nAll notable changes to this project will be documented in this file.\n\nThe format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).\n\n`,
      },
    ],
    [
      "@semantic-release/exec",
      { prepareCmd: "npm version --no-git-tag-version --allow-same-version ${nextRelease.version}" },
    ],
    "@semantic-release/github",
    [
      "@semantic-release/git",
      {
        assets: ["CHANGELOG.md", "package.json", "package-lock.json"],
        message: "chore(release): ${nextRelease.version} [skip ci]\n\n${nextRelease.notes}",
      },
    ],
  ],
};
