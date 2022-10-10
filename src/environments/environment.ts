// This file can be replaced during build by using the `fileReplacements` array.
// `ng build --prod` replaces `environment.ts` with `environment.prod.ts`.
// The list of file replacements can be found in `angular.json`.

import { endpoints, external } from './common';

export const environment = {
	appName: 'Local_Oportunalia',
	production: false,
	url: 'http://asemar.local/api',
	defaultRoute: '/users',
	...external,
};

export function endpoint(key: string, subs: object = {}) {

	let url;

	if (endpoints[key]) {
		url = `${environment.url}${endpoints[key]}`;
	} else {
		url = key;
	}

	const subkeys = Object.keys(subs);
	let urlparams = [];

	if (subkeys.length > 0) {
		for (const key of subkeys) {

			let prevurl = url;
			url = url.replace(`:${key}`, subs[key]);
			if (prevurl == url) {
				urlparams.push(key);
			}
		}
	}

	if (urlparams.length > 0) {
		url += '?'+urlparams.map(key => `${key}=${subs[key]}`).join('&');
	}

	// TODO-DEBUG: review URL
	// console.log(url);
	return url;
}

/*
 * For easier debugging in development mode, you can import the following file
 * to ignore zone related error stack frames such as `zone.run`, `zoneDelegate.invokeTask`.
 *
 * This import should be commented out in production mode because it will have a negative impact
 * on performance if an error is thrown.
 */
// import 'zone.js/dist/zone-error';  // Included with Angular CLI.
