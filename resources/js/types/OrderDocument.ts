export interface OrderDocument {
  id: string;
  order_id: string;
  file_path: string;
  file_path_url: string | null;
  original_name: string;
  mime_type: string;
  size: number;
  created_at: string;
  updated_at: string;
}