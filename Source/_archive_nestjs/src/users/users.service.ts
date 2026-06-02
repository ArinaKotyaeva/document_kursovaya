import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import * as bcrypt from 'bcrypt';
import { Repository } from 'typeorm';
import { AppUser } from './entities/app-user.entity';
import { Role } from './entities/role.entity';
import { CreateAppUserDto } from './dto/create-app-user.dto';
import { UpdateAppUserDto } from './dto/update-app-user.dto';

@Injectable()
export class UsersService {
  constructor(
    @InjectRepository(AppUser)
    private readonly usersRepo: Repository<AppUser>,
    @InjectRepository(Role)
    private readonly rolesRepo: Repository<Role>,
  ) {}

  findAll() {
    return this.usersRepo.find({ order: { id: 'ASC' } });
  }

  async findOne(id: number) {
    const user = await this.usersRepo.findOne({ where: { id } });
    if (!user) {
      throw new NotFoundException('App user not found');
    }
    return user;
  }

  async create(dto: CreateAppUserDto) {
    const role = await this.rolesRepo.findOne({ where: { code: dto.roleCode } });
    if (!role) {
      throw new NotFoundException('Role not found');
    }

    const passwordHash = await bcrypt.hash(dto.password, 10);
    const user = this.usersRepo.create({
      email: dto.email,
      fullName: dto.fullName,
      passwordHash,
      roleId: role.id,
    });

    return this.usersRepo.save(user);
  }

  async update(id: number, dto: UpdateAppUserDto) {
    const user = await this.findOne(id);

    if (dto.fullName) {
      user.fullName = dto.fullName;
    }

    if (dto.roleCode) {
      const role = await this.rolesRepo.findOne({ where: { code: dto.roleCode } });
      if (!role) {
        throw new NotFoundException('Role not found');
      }
      user.roleId = role.id;
    }

    if (dto.isActive !== undefined) {
      user.isActive = dto.isActive;
    }

    if (dto.password) {
      user.passwordHash = await bcrypt.hash(dto.password, 10);
    }

    return this.usersRepo.save(user);
  }

  async remove(id: number) {
    const user = await this.findOne(id);
    user.isActive = false;
    return this.usersRepo.save(user);
  }
}
