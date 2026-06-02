import { PartialType } from '@nestjs/swagger';
import { CreateRegistryDto } from './create-registry.dto';

export class UpdateRegistryDto extends PartialType(CreateRegistryDto) {}
