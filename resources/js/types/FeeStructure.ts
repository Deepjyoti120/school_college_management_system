import { SchoolClass } from "./SchoolClass";

export interface FeeStructure {
  id: string;
  school_id: string;
  academic_year_id: string;
  class_id: string;
  name: number;
  year: number;
  month: string;
  type: string;
  type_label: string;
  type_color: string;
  amount: string;
  gst_amount: string;
  total_amount: string;
  frequency: string;
  frequency_label: string;
  frequency_color: string;
  description?: string | null;
  month_name?: string;
  class: SchoolClass;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}
