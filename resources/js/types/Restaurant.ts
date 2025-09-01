import { User } from "./User";

export interface Restaurant {
  id: string;
  owner_id: string;
  owner?: User;

  address?: string | null;
  city?: string | null;
  state?: string | null;
  pincode?: string | null;
  country: string;

  latitude?: number | null;
  longitude?: number | null;

  is_default: boolean;
  cover_image?: string | null;
  logo?: string | null;
  name: string;
  phone?: string | null;
  description?: string | null;
  is_open: boolean;
  is_active: boolean;

  created_at: string;
  updated_at: string;
}