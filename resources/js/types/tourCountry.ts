import {Tour} from "./tour";

export interface TourCountry {
    id?: bigint
    slug?: string
    name?: string
    description?: string
    image_path?: string
    image?: File,
    tours?: Tour[]
}