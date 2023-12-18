import {TourCountry} from "./tourCountry";

export interface TourCity {
    id?: bigint
    country_id?: bigint
    country?: TourCountry
    name?: string
    description?: string
    image_path?: string
    image?: File
}