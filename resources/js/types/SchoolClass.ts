import { School } from "./school";
import { User } from "./User";

export interface SchoolClass {
  id: string;
  school_id: string;
  name: string;
  is_active?: boolean | null;
  school?: School | null;
  students?: User[] | null;
  teachers?: User[] | null; 
  created_at: string;
  updated_at: string;
}
