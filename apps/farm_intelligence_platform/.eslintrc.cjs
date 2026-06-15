/** @type {import("eslint").Linter.Config} */
module.exports = {
	root: true,
	extends: ['@nextcloud/eslint-config'],
	env: { browser: true, node: true, es2022: true },
	parserOptions: { ecmaVersion: 2022, sourceType: 'module' },
	ignorePatterns: ['vendor/**', 'node_modules/**', 'build/**', 'dist/**'],
}
