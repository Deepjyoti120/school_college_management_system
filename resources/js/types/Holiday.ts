import { AcademicYear } from "./AcademicYear";
import { School } from "./school";
import { User } from "./User";

export interface Holiday {
    id: string;
    user_id?: string;
    school_id: string;
    academic_year_id: string;
    date: string;
    description?: string | null;
    name?: string;
    is_sunday: boolean;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    user?: User | null;
    school?: School | null;
    academicYear?: AcademicYear | null;
}