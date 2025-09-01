import { BadgeVariant } from "./enums"
import { Order } from "./Order"
import { User } from "./User"

export interface OrderProgress {
  id: string
  order_id: string | null
  updated_by: string | null

  stage: number

  status: string
  status_label?: string;
  status_color?: BadgeVariant
  
  title?: string | null
  remarks?: string | null

  created_at: string
  updated_at: string
  created_at_formatted: string
  updated_at_formatted: string

  order?: Order
  updater?: User

}