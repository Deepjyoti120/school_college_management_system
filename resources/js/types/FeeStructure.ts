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
  total_payable: string;
  total_paid: string;
  pending_amount: string;
  frequency: string;
  frequency_label: string;
  frequency_color: string;
  payment_status_color: string;
  payment_status_label: string;
  description?: string;
  month_name?: string;
  class: SchoolClass;
  is_active: boolean;
  is_paid: boolean;
  created_at: string;
  updated_at: string;
}
