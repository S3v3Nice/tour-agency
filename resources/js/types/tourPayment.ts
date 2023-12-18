import {TourBooking} from "./tourBooking";

export interface TourPayment {
    id?: bigint
    booking_id: bigint
    booking: TourBooking
    amount: number
    created_at?: string
    updated_at?: string
}