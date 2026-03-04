import { SchoolClass } from "./SchoolClass";

export interface Subject {
  id: string;
  school_id: string;
  class_id: string;
  name: string;
  code?: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  class?: SchoolClass | null;
}
