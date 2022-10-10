
import { endpoints, external } from './common';

export const environment = {
	appName: 'Oportunalia',
	production: true,
	url: 'https://www.oportunalia.com/api',
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
