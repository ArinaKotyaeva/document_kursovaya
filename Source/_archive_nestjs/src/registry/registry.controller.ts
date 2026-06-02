import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  ParseIntPipe,
  Patch,
  Post,
  UseGuards,
} from '@nestjs/common';
import {
  ApiBearerAuth,
  ApiOperation,
  ApiTags,
} from '@nestjs/swagger';
import { CurrentUser } from '../auth/decorators/current-user.decorator';
import { Roles } from '../auth/decorators/roles.decorator';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../auth/guards/roles.guard';
import { JwtPayload } from '../auth/jwt.strategy';
import { CreateRegistryDto } from './dto/create-registry.dto';
import { UpdateRegistryDto } from './dto/update-registry.dto';
import { RegistryService } from './registry.service';

@ApiTags('users')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard, RolesGuard)
@Controller('users')
export class RegistryController {
  constructor(private readonly registryService: RegistryService) {}

  @Get()
  @ApiOperation({ summary: 'Список учётных карточек реестра' })
  findAll() {
    return this.registryService.findAll();
  }

  @Get(':id')
  @ApiOperation({ summary: 'Одна учётная карточка по id' })
  findOne(@Param('id', ParseIntPipe) id: number) {
    return this.registryService.findOne(id);
  }

  @Post()
  @Roles('admin')
  @ApiOperation({ summary: 'Создать учётную карточку (admin)' })
  create(@Body() dto: CreateRegistryDto, @CurrentUser() user: JwtPayload) {
    return this.registryService.create(dto, user.sub);
  }

  @Patch(':id')
  @Roles('admin')
  @ApiOperation({ summary: 'Изменить учётную карточку (admin)' })
  update(
    @Param('id', ParseIntPipe) id: number,
    @Body() dto: UpdateRegistryDto,
  ) {
    return this.registryService.update(id, dto);
  }

  @Delete(':id')
  @Roles('admin')
  @ApiOperation({ summary: 'Удалить учётную карточку (admin)' })
  remove(
    @Param('id', ParseIntPipe) id: number,
    @CurrentUser() user: JwtPayload,
  ) {
    return this.registryService.remove(id, user.sub);
  }
}
