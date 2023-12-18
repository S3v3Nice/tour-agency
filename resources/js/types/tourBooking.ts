import {Tour} from "./tour";
import {User} from "./user";

export interface TourBooking {
    id?: bigint
    user_id?: bigint
    user?: User
    tour_id?: bigint
    tour?: Tour
    adults_count?: number
    children_count?: number
    is_verified?: boolean
    created_at?: string
    updated_at?: string
}