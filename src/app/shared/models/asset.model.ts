
export interface Asset {
	active_category: string,
	active_category_id: number,
	active_condition_id: number,
	auction_id: number,
	auction_status_id: number,
	auction_type_id: number,
	address: string,
	area: number,
	city: string,
	created_at: string,
	id: number,
	images: Array<string>,
	image_paths: Array<string>,
	province: string,
	province_id: number,
	refund: boolean,
	name: string,
}

export interface AssetCategory {
	created_at: string,
	description: string,
	id: number,
	image: string,
	image_path: string,
	name: string,
}

export interface Condition {
	id: number,
	name: string,
}
