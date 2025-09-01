export interface Product {
  id: string;
  name: string;
  description: string;
  price: number; 
  stock: number;
  sku: string;
  image_url: string | null;
  is_active: boolean;
}