import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { RegistryRecord } from './entities/registry-record.entity';
import { RegistryController } from './registry.controller';
import { RegistryService } from './registry.service';

@Module({
  imports: [TypeOrmModule.forFeature([RegistryRecord])],
  controllers: [RegistryController],
  providers: [RegistryService],
  exports: [RegistryService],
})
export class RegistryModule {}
