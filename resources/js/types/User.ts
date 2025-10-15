import { RazorpayPaymentStatus, UserRole } from "./enums";
import { Payment } from "./Payment";
import { School } from "./school";
import { SchoolClass } from "./SchoolClass";
import { SchoolSection } from "./SchoolSection";

export interface User {
  id: string;
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
  school?: School | null; 
  class?: SchoolClass | null; 
  section?: SchoolSection | null; 
  dob_formatted?: string | null;
  doj_formatted?: string | null;
  payment_status?: RazorpayPaymentStatus | null;
  roll_number?: string | null;
  payment?: Payment | null;
}
