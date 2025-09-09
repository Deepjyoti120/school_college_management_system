export interface School {
  id: string;
  name: string;
  address: string;
  pin: string;
  district: string;
  state: string;
  country: string;
  phone: string;
  email: string;
  website: string;
  latitude: number | null;
  longitude: number | null;
  logo_path: string | null;
  cover_path: string | null;
  is_active: boolean;

  logo_url?: string | null;
  cover_url?: string | null;
}
