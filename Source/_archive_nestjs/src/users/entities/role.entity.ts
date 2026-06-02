import { Column, Entity, OneToMany, PrimaryGeneratedColumn } from 'typeorm';
import { AppUser } from './app-user.entity';

@Entity({ name: 'roles' })
export class Role {
  @PrimaryGeneratedColumn()
  id!: number;

  @Column({ length: 32, unique: true })
  code!: string;

  @Column({ length: 100 })
  name!: string;

  @OneToMany(() => AppUser, (user) => user.role)
  users!: AppUser[];
}
