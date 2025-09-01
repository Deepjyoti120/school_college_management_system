import { UserRole } from "./enums";

export interface User {
  name: string;
  email: string;
  password: string;
  dob: string;
  doj: string;
  role: UserRole;
  phone: string | null;
  latitude: number | null;
  longitude: number | null;
  profile_photo: string | null;
  is_active: boolean | null;
  country_code: string | null;
  device_info: Record<string, any> | null;
  fcm_token: string | null;
  role_label?: string;
  role_color?: string;
  profile_url?: string | null;
}
