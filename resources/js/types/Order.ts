import { BadgeVariant } from './BadgeVariant';
import { OrderStatus } from './enums';
import { OrderDocument } from './OrderDocument';
import { OrderProgress } from './OrderProgress';
import { Product } from './Product';
import { User } from './User';

export interface Order {
  id: string;
  product_id: string;
  created_by: number;
  updated_by: number | null;
  total_price: string;
  order_number: string;
  quantity: number;
  stage: number;
  status: OrderStatus;
  new_status: OrderStatus;
  show_action_button: boolean;
  created_at: string;
  updated_at: string;
  product?: Product;
  documents?: OrderDocument[];
  status_label?: string;
  status_color?: BadgeVariant;
  creator?: User;
  updater?: User;
  progresses?: OrderProgress[];
  latestProgress?: OrderProgress;

  driver_name?: string | null
  vehicle_number?: string | null
  challan_no?: string | null
  country_code?: string | null
  driver_phone?: string | null
}