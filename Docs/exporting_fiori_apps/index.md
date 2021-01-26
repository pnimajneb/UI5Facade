# Exporting standalone SAP Fiori web apps

1. [Export an app to a folder on your PC](export_fiori_app.md)
2. [Upload the app to SAP Netweaver](deploy_on_netweaver.md)
3. [Add the App to the Fiori Launchpad](deploy_on_fiori_launchpad.md)

## Testing exported apps	

If the app is not quite stable yet, it can be very helpful to test it locally before deploying to SAP. Here is how:

1. Change the configuration of the app as follows:
	- `Relative URLs in OData` - OFF
	- `Export credentials to manifest.json` - ON
	- `Export SAP client to manifest.json` - ON
	- If using a version control system (git, SVN) on the export folder, use another folder temporarily
2. Export the app 
3. Start Chrome in no-CORS mode avoid AJAX-requests to SAP being blocked.
	- Use a portable version of Chrome (as of january 2021 you need [Chrome 79](https://www.filehorse.com/download-google-chrome-portable-64/46659/) or less - newer versions block SAP cookis in no-CORS mode).
	- Create a separate folder for the user data: e.g. `c:\noCorsBrowserData`
	- Create special shortcut: `GoogleChromePortable.exe --disable-web-security --disable-gpu --disable-features=CookiesWithoutSameSiteMustBeSecure --user-data-dir="c:\noCorsBrowserData"`
4. Open the app there (preferably using a web server and `http://localhost/...`)
5. Remember to revert changes if the export configuration after testing. 
