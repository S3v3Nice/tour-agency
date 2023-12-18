import {TourCity} from "./tourCity";

export interface TourHotel {
    id?: bigint
    city_id?: bigint
    city?: TourCity
    name?: string
}