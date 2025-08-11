
export const endpoints = {

	// Auth
	login: '/auth/login/admin',
	logout: '/auth/logout',
	recover_password: '/auth/recover-password',

	// Selectors Data
	auction_assets_list: '/admin/active',
	asset_categories_list: '/admin/active_category',
	conditions_list: '/admin/active/conditions',
	countries_list: '/country',
	newsletter_templates_list: '/admin/newsletter_template',
	provinces_list: '/province/:country',
	representation_types_list: '/representation_type',
	roles_list: '/admin/role',
  max_id: '/admin/max_id',

	// Users
	users_dt: '/admin/user',
	users_export: '/admin/user/export/:type',
	users_create: '/admin/user',
	users_get: '/admin/user/:id',
	users_confirm: '/admin/user/:id/confirm',
	users_validate: '/admin/user/:id/validate',
	user_auctions_dt: '/admin/user/:id/bids',
	user_deposits_dt: '/admin/user/:id/deposits',
	user_direct_sellings_dt: '/admin/user/:id/offers', // estos serian solo venta directa
	user_cesions_dt: '/admin/user/:id/offers', // aqui para el futuro, habria que identificar que estos bids son de cesiones de remate
	user_representations_dt: '/admin/user/:id/representation',
	users_del_doc1: '/admin/user/:id/documentone',
	users_del_doc2: '/admin/user/:id/documenttwo',

	// Deposits
	deposits_dt: '/admin/deposit',
	deposits_export: '/admin/deposit/export/:type',
	deposits_validate: '/admin/deposit/:id/verify',

	// Representations
	representations_dt: '/admin/representation',
	representations_export: '/admin/representation/export/:type',
	representations_create: '/admin/representation',
	representations_get: '/admin/representation/:id',
	representations_validate: '/admin/representation/:id/validate',

	// Assets
	assets_dt: '/admin/active',
	assets_export: '/admin/active/export/:type',
	assets_create: '/admin/active',
	assets_get: '/admin/active/:id',
	assets_image_get: '/admin/active/image/:id',
	assets_duplicate: '/admin/active/duplicate/:id',

	// Asset Categories
	asset_categories_dt: '/admin/active_category',
	asset_categories_export: '/admin/active_category/export/:type',
	asset_categories_create: '/admin/active_category',
	asset_categories_get: '/admin/active_category/:id',

	// Auctions
	auctions_dt: '/admin/auction',
	auctions_export: '/admin/auction/export/:type',
	auctions_create: '/admin/auction',
	auctions_get: '/admin/auction/:id',
	auction_history_dt: '/admin/auction/:id/history',
	auction_users_dt: '/admin/auction/:id/bids',
	auction_deposits_dt: '/admin/auction/:id/deposits',
	auction_activity: '/admin/auction/:id/activity',
	auction_featured: '/admin/auction/:id/featured',
	auction_asignado: '/admin/auction/:id/asignado',
	auction_final_report: '/admin/auction/:id/final_report',
	direct_sale_final_report: '/admin/auction/:id/direct_sale_final_report',
  cesion_final_report: '/admin/auction/:id/cesion_final_report',
	auction_duplicate: '/admin/auction/duplicate/:id',
	delete_document: '/admin/auction/:id/deletedocument',

	// Direct Sellings
	direct_sellings_dt: '/admin/direct_sale',
	direct_sellings_export: '/admin/direct_sale/export/:type',
	direct_sellings_create: '/admin/direct_sale',
	direct_sellings_get: '/admin/direct_sale/:id',
	direct_selling_history_dt: '/admin/direct_sale/:id/history',
	direct_selling_users_dt: '/admin/direct_sale/:id/offers',
	direct_selling_featured: '/admin/direct_sale/:id/featured',
	direct_selling_asignado: '/admin/direct_sale/:id/asignado',
	direct_selling_duplicate: '/admin/direct_sale/duplicate/:id',
	offers_validate: '/admin/offer/:id/status',

  // Cesions
  cesions_dt: '/admin/cesion',
	cesions_export: '/admin/cesion/export/:type',
  cesions_create: '/admin/cesion',
	cesions_get: '/admin/cesion/:id',
	cesion_history_dt: '/admin/cesion/:id/history',
	cesion_users_dt: '/admin/cesion/:id/offers',
	cesion_featured: '/admin/cesion/:id/featured',
	cesion_asignado: '/admin/cesion/:id/asignado',
	cesion_duplicate: '/admin/cesion/duplicate/:id',

  // Cesions Credito
  cesions_credito_dt: '/admin/credit-assignment',
	cesions_credito_export: '/admin/credit-assignment/export/:type',
  cesions_credito_create: '/admin/credit-assignment',
	cesions_credito_get: '/admin/credit-assignment/:id',
	cesion_credito_history_dt: '/admin/credit-assignment/:id/history',
	cesion_credito_users_dt: '/admin/credit-assignment/:id/offers',
	cesion_credito_featured: '/admin/credit-assignment/:id/featured',
	cesion_credito_asignado: '/admin/credit-assignment/:id/asignado',
	cesion_credito_duplicate: '/admin/credit-assignment/duplicate/:id',
	cesion_credito_final_report: '/admin/credit-assignment/:id/final_report',
	cesion_credito_deposits_dt: '/admin/credit-assignment/:id/deposits',
	offer_cesion_credito_validate: '/admin/offer_credit_assignment/:id/status',
	cesion_credito_public: '/credit-assignment/:id',


	// Newsletters
	newsletters_dt: '/admin/newsletter',
	newsletters_export: '/admin/newsletter/export/:type',
	newsletters_create: '/admin/newsletter',
	newsletters_get: '/admin/newsletter/:id',

	// Newsletter Templates
	newsletter_templates_dt: '/admin/newsletter_template',
	newsletter_templates_export: '/admin/newsletter_template/export/:type',
	newsletter_templates_create: '/admin/newsletter_template',
	newsletter_templates_get: '/admin/newsletter_template/:id',

	// Blog
	blog_dt: '/admin/blog',
	blog_export: '/admin/blog/export/:type',
	blog_create: '/admin/blog',
	blog_get: '/admin/blog/:id',

	//Membresia
	membresia_dt: '/admin/membresia',
	membresia_create: '/admin/membresia',
	membresia_get: '/admin/membresia/:id',
	membresia_users: '/admin/membresia/users',
	membresia_auctions: '/admin/membresia/auctions',

	// Notifications
	notifications: '/admin/notification',
	notifications_status_all: '/admin/notification/all/:key',
	notifications_status: '/admin/notification/:id',

	actives_import: '/activesImport',
	auctions_import: '/auctionsImport',
};

export const external = {

	// Google Maps API
	google_maps: 'https://www.google.com/maps?q=:query',
	google_maps_geocode: 'https://maps.googleapis.com/maps/api/geocode/json?address=:address&key=:key',
	google_maps_key: 'AIzaSyBKqyCklbtfn_MpluFTmkyCBF13J6vntEs',
};
