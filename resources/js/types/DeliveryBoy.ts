import { VehicleType } from "./enums";

export interface DeliveryBoy {
  id: string;
  user_id: string;

  vehicle_type: VehicleType | null;
  vehicle_number: string | null;
  license_number: string | null;
  license_expiry: string | null;

  is_available: boolean;
  last_active_at: string | null;

  created_at: string;
  updated_at: string;
}