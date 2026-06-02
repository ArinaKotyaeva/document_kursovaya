import { ApiPropertyOptional } from '@nestjs/swagger';
import { IsBoolean, IsIn, IsOptional, IsString, MinLength } from 'class-validator';

export class UpdateAppUserDto {
  @ApiPropertyOptional()
  @IsOptional()
  @IsString()
  @MinLength(2)
  fullName?: string;

  @ApiPropertyOptional({ enum: ['admin', 'guest'] })
  @IsOptional()
  @IsIn(['admin', 'guest'])
  roleCode?: string;

  @ApiPropertyOptional()
  @IsOptional()
  @IsBoolean()
  isActive?: boolean;

  @ApiPropertyOptional()
  @IsOptional()
  @IsString()
  @MinLength(4)
  password?: string;
}
