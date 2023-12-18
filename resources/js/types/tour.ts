import {TourHotel} from "./tourHotel";

export interface Tour {
    id?: bigint
    hotel_id?: bigint
    hotel?: TourHotel
    start_date: string
    end_date: string
    max_participant_count: number
    participant_count: number
    adult_price: number
}
