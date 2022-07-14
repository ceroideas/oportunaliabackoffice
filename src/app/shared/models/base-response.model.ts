
export interface BaseResponse<T> {
	code: number,
	messages: Array<string>,
	response: T,
	time: string,
	total: number,
}

export interface ErrorResponse {
	error: {
		code: number,
		messages: Array<string>,
		time: string,
		total: number,
	}
}
