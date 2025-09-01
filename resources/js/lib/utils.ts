import { type ClassValue, clsx } from 'clsx'
import { FileText, ImageIcon } from 'lucide-vue-next';
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export class Utils {
  static slugify(str: string): string {
    return str
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
      .replace(/\s+/g, '-')        // collapse whitespace and replace by -
      .replace(/-+/g, '-');        // collapse dashes
  }

  static formatFileSize = (size: number) => {
    if (size < 1024) return `${size} B`
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`
    return `${(size / (1024 * 1024)).toFixed(1)} MB`
  }
  static getFileIcon = (mimeType: string) => {
    if (mimeType.includes('pdf')) return FileText
    if (mimeType.startsWith('image/')) return ImageIcon
    return FileText;
  }
 static pluralize = (count: number, singular: string, plural?: string) => {
  return count === 1 ? singular : (plural || singular + 's')
}
}