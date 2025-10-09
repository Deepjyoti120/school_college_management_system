import { School } from "./school";

export interface SchoolSection {
  id: string;
  school_id: string;
  name: string;
  is_active: boolean;
  school: School;
  created_at: string;
  updated_at: string;
}
