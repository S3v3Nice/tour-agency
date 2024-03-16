export interface User {
    id?: bigint
    email?: string
    first_name?: string
    last_name?: string
    is_admin?: boolean
    created_at?: string
    updated_at?: string
}

export interface TourCountry {
    id?: bigint
    slug?: string
    name?: string
    description?: string
    image_path?: string
    image?: File,
    tours?: Tour[]
}

export interface TourCity {
    id?: bigint
    country_id?: bigint
    country?: TourCountry
    name?: string
    description?: string
    image_path?: string
    image?: File
}

export interface TourHotel {
    id?: bigint
    city_id?: bigint
    city?: TourCity
    name?: string
}

export interface Tour {
    id?: bigint
    hotel_id?: bigint
    hotel?: TourHotel
    start_date?: string
    end_date?: string
    max_participant_count?: number
    participant_count?: number
    adult_price?: number
}

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

export interface TourPayment {
    id?: bigint
    booking_id?: bigint
    booking?: TourBooking
    amount?: number
    created_at?: string
    updated_at?: string
}
