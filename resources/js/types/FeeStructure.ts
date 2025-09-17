export interface FeeStructure {
  id: string;
  school_id: string;
  academic_year_id: string;
  class_id: string;
  name: string;
  type: string;
  type_label: string;
  type_color: string;
  amount: string;
  frequency: string;
  frequency_label: string;
  frequency_color: string;
  description?: string | null;
  created_at: string;
  updated_at: string;
}
