import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { DataSource, Repository } from 'typeorm';
import { RegistryRecord } from './entities/registry-record.entity';
import { CreateRegistryDto } from './dto/create-registry.dto';
import { UpdateRegistryDto } from './dto/update-registry.dto';

@Injectable()
export class RegistryService {
  constructor(
    @InjectRepository(RegistryRecord)
    private readonly registryRepo: Repository<RegistryRecord>,
    private readonly dataSource: DataSource,
  ) {}

  findAll() {
    return this.registryRepo.find({
      where: { isDeleted: false },
      order: { id: 'ASC' },
    });
  }

  async findOne(id: number) {
    const record = await this.registryRepo.findOne({
      where: { id, isDeleted: false },
    });

    if (!record) {
      throw new NotFoundException('User not found');
    }

    return record;
  }

  async create(dto: CreateRegistryDto, userId: number) {
    const record = this.registryRepo.create({
      name: dto.name.trim(),
      email: dto.email.trim(),
      age: dto.age ?? null,
      createdBy: userId,
    });

    return this.registryRepo.save(record);
  }

  async update(id: number, dto: UpdateRegistryDto) {
    const record = await this.findOne(id);
    Object.assign(record, {
      name: dto.name?.trim() ?? record.name,
      email: dto.email?.trim() ?? record.email,
      age: dto.age === undefined ? record.age : dto.age ?? null,
    });
    return this.registryRepo.save(record);
  }

  async remove(id: number, userId: number) {
    await this.findOne(id);
    await this.dataSource.query('CALL sp_registry_soft_delete($1, $2)', [id, userId]);
    return { success: true, id };
  }
}
