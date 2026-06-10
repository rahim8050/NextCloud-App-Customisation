# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e/page-picker.spec.ts >> Custom page picker >> cross-collective search >> link to page from other collective
- Location: playwright/e2e/page-picker.spec.ts:63:3

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: locator.click: Target page, context or browser has been closed
Call log:
  - waiting for locator('.collectives-page-picker').locator('.searchbar [aria-haspopup="menu"]')

```

# Page snapshot

```yaml
- generic [ref=e1]:
  - generic [ref=e3]:
    - generic [ref=e4]: Keyboard navigation help
    - generic [ref=e5]:
      - link "Skip to app navigation" [ref=e6] [cursor=pointer]:
        - /url: "#app-navigation-vue"
        - generic [ref=e8]: Skip to app navigation
      - link "Skip to main content" [ref=e9] [cursor=pointer]:
        - /url: "#app-content-vue"
        - generic [ref=e11]: Skip to main content
    - img [ref=e12]:
      - img [ref=e14]
  - banner [ref=e35]:
    - generic [ref=e36]:
      - link "Go to Dashboard" [ref=e37] [cursor=pointer]:
        - /url: /index.php
      - navigation "Applications menu" [ref=e39]:
        - list "Apps" [ref=e40]:
          - listitem [ref=e41]:
            - link "Dashboard" [ref=e42] [cursor=pointer]:
              - /url: /index.php/apps/dashboard/
              - img [ref=e43]
              - generic [ref=e44]: Dashboard
          - listitem [ref=e45]:
            - link "Files" [ref=e46] [cursor=pointer]:
              - /url: /index.php/apps/files/
              - img [ref=e47]
              - generic [ref=e48]: Files
          - listitem [ref=e49]:
            - link "Collectives" [ref=e50] [cursor=pointer]:
              - /url: /index.php/apps/collectives/
              - img [ref=e51]
              - generic [ref=e52]: Collectives
    - generic [ref=e53]:
      - button "Unified search" [ref=e56] [cursor=pointer]:
        - img [ref=e59]:
          - img [ref=e60]
      - button "Search contacts" [ref=e63] [cursor=pointer]:
        - img [ref=e66]:
          - img [ref=e67]
      - navigation "Settings menu" [ref=e69]:
        - button "Settings menu" [ref=e70] [cursor=pointer]:
          - img [ref=e74]:
            - img [ref=e75]
        - generic [ref=e77]: Avatar of i8yro — Online
  - generic [ref=e79]:
    - generic [ref=e80]:
      - navigation [ref=e81]:
        - list [ref=e82]:
          - listitem [ref=e83]:
            - generic [ref=e84]: Select a collective
          - listitem [ref=e85]:
            - generic [ref=e86]:
              - link [ref=e87] [cursor=pointer]:
                - /url: /index.php/apps/collectives/kda4oiba9m-10
                - img [ref=e89]:
                  - img [ref=e90]
                - generic [ref=e97]: kda4oiba9m
              - button [ref=e101] [cursor=pointer]:
                - img [ref=e104]:
                  - img [ref=e105]
          - listitem [ref=e107]:
            - link [ref=e109] [cursor=pointer]:
              - /url: /index.php/apps/collectives/q5jechi1w4-11
              - img [ref=e111]:
                - img [ref=e112]
              - generic [ref=e119]: q5jechi1w4
          - listitem [ref=e121]:
            - button [ref=e123] [cursor=pointer]:
              - generic [ref=e124]:
                - img [ref=e126]:
                  - img [ref=e127]
                - generic [ref=e129]: New collective
        - button [ref=e131] [cursor=pointer]:
          - generic [ref=e132]:
            - img [ref=e134]:
              - img [ref=e135]
            - generic [ref=e137]: Deleted collectives
        - button [ref=e140] [cursor=pointer]:
          - generic [ref=e141]:
            - img [ref=e143]:
              - img [ref=e144]
            - generic [ref=e146]: Collectives settings
      - button "Open navigation" [ref=e148] [cursor=pointer]:
        - img [ref=e151]:
          - img [ref=e152]
    - main [ref=e154]:
      - generic [ref=e156]:
        - generic [ref=e158]:
          - generic [ref=e159]:
            - generic [ref=e162]:
              - textbox "Search pages" [ref=e163] [cursor=pointer]:
                - /placeholder: Search pages…
              - generic: Search pages
            - button "Sort order" [ref=e166] [cursor=pointer]:
              - img [ref=e169]:
                - img [ref=e170]
          - generic [ref=e172]:
            - list
          - generic [ref=e173]:
            - generic [ref=e174] [cursor=pointer]:
              - img [ref=e176]:
                - img [ref=e177]
              - link "kda4oiba9m" [ref=e184]:
                - /url: /index.php/apps/collectives/kda4oiba9m-10
                - generic [ref=e185]: kda4oiba9m
              - generic [ref=e186]:
                - button "Actions" [ref=e190]:
                  - img [ref=e193]:
                    - img [ref=e194]
                - button "Add a page" [ref=e196]:
                  - img [ref=e199]:
                    - img [ref=e200]
            - generic [ref=e202]:
              - generic [ref=e204] [cursor=pointer]:
                - img [ref=e206]:
                  - img [ref=e207]
                - link "Target page" [ref=e209]:
                  - /url: /index.php/apps/collectives/kda4oiba9m-10/Target-page-170
                  - generic [ref=e210]: Target page
              - generic [ref=e212] [cursor=pointer]:
                - img [ref=e214]:
                  - img [ref=e215]
                - link "Source page" [ref=e217]:
                  - /url: /index.php/apps/collectives/kda4oiba9m-10/Source-page-169
                  - generic [ref=e218]: Source page
                - generic [ref=e219]:
                  - button "Actions" [ref=e223]:
                    - img [ref=e226]:
                      - img [ref=e227]
                  - button "Add a subpage" [ref=e229]:
                    - img [ref=e232]:
                      - img [ref=e233]
          - button "Show deleted pages" [ref=e236] [cursor=pointer]:
            - generic [ref=e237]:
              - img [ref=e239]:
                - img [ref=e240]
              - generic [ref=e242]: Deleted pages
        - generic [ref=e246]:
          - generic [ref=e247]:
            - button "Select emoji for page" [ref=e250] [cursor=pointer]:
              - img [ref=e253]:
                - img [ref=e254]
            - heading "Source page" [level=2] [ref=e257]:
              - textbox "Title" [ref=e258] [cursor=pointer]: Source page
            - generic [ref=e259]:
              - button "Stop editing" [ref=e261] [cursor=pointer]:
                - generic [ref=e262]:
                  - img [ref=e264]:
                    - img [ref=e265]
                  - generic [ref=e267]: Preview
              - button "Actions" [ref=e271] [cursor=pointer]:
                - img [ref=e274]:
                  - img [ref=e275]
              - button "Open sidebar" [ref=e277] [cursor=pointer]:
                - img [ref=e280]:
                  - img [ref=e281]
          - generic:
            - list
          - generic [ref=e289]:
            - region "Editor actions" [ref=e290]:
              - toolbar "Formatting menu bar" [ref=e291]:
                - button "Undo" [disabled] [ref=e292]:
                  - img [ref=e295]:
                    - img [ref=e296]
                - button "Redo" [disabled] [ref=e298]:
                  - img [ref=e301]:
                    - img [ref=e302]
                - generic "Headings (Ctrl+Shift+1…6)" [ref=e304]:
                  - button "Headings" [ref=e306] [cursor=pointer]:
                    - img [ref=e309]:
                      - img [ref=e310]
                - button "Bold" [ref=e312] [cursor=pointer]:
                  - img [ref=e315]:
                    - img [ref=e316]
                - button "Italic" [ref=e318] [cursor=pointer]:
                  - img [ref=e321]:
                    - img [ref=e322]
                - button "Underline" [ref=e324] [cursor=pointer]:
                  - img [ref=e327]:
                    - img [ref=e328]
                - button "Strikethrough" [ref=e330] [cursor=pointer]:
                  - img [ref=e333]:
                    - img [ref=e334]
                - button "Highlight" [ref=e336] [cursor=pointer]:
                  - img [ref=e339]:
                    - img [ref=e340]
                - generic "Lists (Ctrl+Shift+7…9)" [ref=e342]:
                  - button "Lists" [ref=e344] [cursor=pointer]:
                    - img [ref=e347]:
                      - img [ref=e348]
                - generic "Blocks" [ref=e350]:
                  - button "Blocks" [ref=e352] [cursor=pointer]:
                    - img [ref=e355]:
                      - img [ref=e356]
                - button "Table" [ref=e358] [cursor=pointer]:
                  - img [ref=e361]:
                    - img [ref=e362]
                - button "Details" [ref=e364] [cursor=pointer]:
                  - img [ref=e367]:
                    - img [ref=e368]
                - generic "Insert link" [ref=e370]:
                  - button "Insert link" [expanded] [ref=e372] [cursor=pointer]:
                    - img [ref=e375]:
                      - img [ref=e376]
                - generic "Insert attachment" [ref=e378]:
                  - button "Insert attachment" [ref=e380] [cursor=pointer]:
                    - img [ref=e383]:
                      - img [ref=e384]
                - menu "Insert emoji" [ref=e388] [cursor=pointer]:
                  - img [ref=e391]:
                    - img [ref=e392]
                - generic "Remaining actions" [ref=e394]:
                  - button "Remaining actions" [ref=e396] [cursor=pointer]:
                    - img [ref=e399]:
                      - img [ref=e400]
              - generic [ref=e403]:
                - generic "Last saved a few seconds ago" [ref=e404]:
                  - button "Save document" [ref=e405] [cursor=pointer]:
                    - img [ref=e408]:
                      - img [ref=e409]
                - button "Active people" [ref=e414] [cursor=pointer]:
                  - img [ref=e417]:
                    - img [ref=e418]
              - menu "Insert link" [ref=e425]:
                - menuitem "Link to page" [ref=e426] [cursor=pointer]:
                  - img [ref=e428]:
                    - img [ref=e429]
                  - generic [ref=e431]: Link to page
                - menuitem "Link to file or folder" [ref=e432] [cursor=pointer]:
                  - img [ref=e433]:
                    - img [ref=e434]
                  - generic [ref=e436]: Link to file or folder
                - menuitem "Link to website" [ref=e437] [cursor=pointer]:
                  - img [ref=e438]:
                    - img [ref=e439]
                  - generic [ref=e441]: Link to website
                - menuitem "Open the Smart Picker" [ref=e442] [cursor=pointer]:
                  - img [ref=e443]:
                    - img [ref=e444]
                  - generic [ref=e446]: Open the Smart Picker
            - document [ref=e449]:
              - textbox [ref=e450] [cursor=pointer]:
                - paragraph [ref=e451]: Start writing or type '/' to add…
              - generic [ref=e452]:
                - button "Insert below" [ref=e453] [cursor=pointer]:
                  - img [ref=e456]:
                    - img [ref=e457]
                - button "Click for options, hold to drag" [ref=e459]:
                  - img [ref=e462]:
                    - img [ref=e463]
            - generic [ref=e466]:
              - button "Link to file or folder" [ref=e467] [cursor=pointer]:
                - generic [ref=e468]:
                  - img [ref=e470]:
                    - img [ref=e471]
                  - generic [ref=e473]: Link to file or folder
              - button "Upload" [ref=e474] [cursor=pointer]:
                - generic [ref=e475]:
                  - img [ref=e477]:
                    - img [ref=e478]
                  - generic [ref=e480]: Upload
              - button "Insert Table" [ref=e481] [cursor=pointer]:
                - generic [ref=e482]:
                  - img [ref=e484]:
                    - img [ref=e485]
                  - generic [ref=e487]: Insert Table
              - button "Smart Picker" [ref=e488] [cursor=pointer]:
                - generic [ref=e489]:
                  - img [ref=e491]:
                    - img [ref=e492]
                  - generic [ref=e494]: Smart Picker
  - dialog [ref=e495]:
    - generic [ref=e498]:
      - generic [ref=e500]:
        - button "Close Smart Picker" [active] [ref=e501] [cursor=pointer]:
          - img [ref=e504]:
            - img [ref=e505]
        - generic [ref=e510]:
          - heading "Add link to page" [level=2] [ref=e511]
          - generic [ref=e512]:
            - generic [ref=e515]:
              - textbox "Search pages…" [ref=e516] [cursor=pointer]:
                - /placeholder: ""
              - generic: Search pages…
            - list [ref=e518]:
              - generic [ref=e519] [cursor=pointer]:
                - img [ref=e521]:
                  - img [ref=e522]
                - generic [ref=e524]:
                  - strong [ref=e526]: Landing page
                  - generic [ref=e527]: In collective kda4oiba9m
              - generic [ref=e528] [cursor=pointer]:
                - img [ref=e530]:
                  - img [ref=e531]
                - generic [ref=e533]:
                  - strong [ref=e535]: Source page
                  - generic [ref=e536]: In collective kda4oiba9m
              - generic [ref=e537] [cursor=pointer]:
                - img [ref=e539]:
                  - img [ref=e540]
                - generic [ref=e542]:
                  - strong [ref=e544]: Target page
                  - generic [ref=e545]: In collective kda4oiba9m
              - generic [ref=e546] [cursor=pointer]:
                - img [ref=e548]:
                  - img [ref=e549]
                - generic [ref=e551]:
                  - strong [ref=e553]: Landing page
                  - generic [ref=e554]: In collective q5jechi1w4
            - button "Cancel" [ref=e556] [cursor=pointer]:
              - generic [ref=e558]: Cancel
      - button "Close" [ref=e559] [cursor=pointer]:
        - img [ref=e562]:
          - img [ref=e563]
```

# Test source

```ts
  1  | /**
  2  |  * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
  3  |  * SPDX-License-Identifier: AGPL-3.0-or-later
  4  |  */
  5  | 
  6  | import type { CollectivePage } from '../support/fixtures/CollectivePage.ts'
  7  | 
  8  | import { expect, mergeTests } from '@playwright/test'
  9  | import { test as createCollectiveTest } from '../support/fixtures/create-collectives.ts'
  10 | import { test as editorTest } from '../support/fixtures/editor.ts'
  11 | import { randomString } from '../support/helpers/randomString.ts'
  12 | 
  13 | const test = mergeTests(createCollectiveTest, editorTest)
  14 | 
  15 | test.describe('Custom page picker', () => {
  16 | 	let targetPage: CollectivePage
  17 | 
  18 | 	test.beforeEach(async ({ user, page, collective, editor }) => {
  19 | 		const sourcePage = await collective.createPage({ title: 'Source page', user, page })
  20 | 		targetPage = await collective.createPage({ title: 'Target page', user, page })
  21 | 		await sourcePage.open(true)
  22 | 
  23 | 		editor.setMode(true)
  24 | 		await editor.clickMenu('Insert link', 'Link to page')
  25 | 		await page.waitForTimeout(200)
  26 | 	})
  27 | 
  28 | 	test('link to local page via search', async ({ page, editor }) => {
  29 | 		const targetPageItem = editor.pagePicker.locator('.page-preview-item').filter({ hasText: 'Target page' })
  30 | 		// Should already be listed without searching
  31 | 		await expect(targetPageItem).toBeVisible()
  32 | 
  33 | 		await editor.pagePickerSearch.pressSequentially('Target page')
  34 | 
  35 | 		// Still listed with searching
  36 | 		await expect(targetPageItem).toBeVisible()
  37 | 
  38 | 		await targetPageItem.click()
  39 | 
  40 | 		const pageWidget = editor.getContent().locator('.widget-custom a.collective-page')
  41 | 		await expect(pageWidget).toBeVisible()
  42 | 		const origin = new URL(page.url()).origin
  43 | 		await expect(pageWidget).toHaveAttribute('href', origin + targetPage.getPageUrl())
  44 | 	})
  45 | 
  46 | 	test.describe('cross-collective search', () => {
  47 | 		test.use({
  48 | 			// eslint-disable-next-line no-empty-pattern
  49 | 			collectiveConfigs: async ({}, use) => {
  50 | 				await use([
  51 | 					{ name: randomString() },
  52 | 					{ name: randomString() },
  53 | 				])
  54 | 			},
  55 | 		})
  56 | 
  57 | 		let otherTargetPage: CollectivePage
  58 | 
  59 | 		test.beforeEach(async ({ user, page, collectives }) => {
  60 | 			otherTargetPage = await collectives[1].createPage({ title: 'Other collective page', user, page, content: 'content' })
  61 | 		})
  62 | 
  63 | 		test('link to page from other collective', async ({ page, editor }) => {
> 64 | 			await editor.pagePicker.locator('.searchbar [aria-haspopup="menu"]').click()
     |                                                                         ^ Error: locator.click: Target page, context or browser has been closed
  65 | 			await page.getByText('Limit to current collective').click()
  66 | 
  67 | 			// Search for the page in the other collective
  68 | 			await editor.pagePickerSearch.pressSequentially('Other collective page')
  69 | 
  70 | 			const otherPageItem = editor.pagePicker.locator('.page-preview-item').filter({ hasText: 'Other collective page' })
  71 | 			await expect(otherPageItem).toBeVisible()
  72 | 
  73 | 			await otherPageItem.click()
  74 | 
  75 | 			const pageWidget = editor.getContent().locator('.widget-custom a.collective-page')
  76 | 			await expect(pageWidget).toBeVisible()
  77 | 			const origin = new URL(page.url()).origin
  78 | 			await expect(pageWidget).toHaveAttribute('href', origin + otherTargetPage.getPageUrl())
  79 | 		})
  80 | 	})
  81 | })
  82 | 
```