import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';
import { IsEmail, IsInt, IsOptional, IsPositive, IsString, MaxLength, MinLength } from 'class-validator';

export class CreateRegistryDto {
  @ApiProperty({ example: 'Иван Иванов' })
  @IsString()
  @MinLength(1)
  @MaxLength(100)
  name!: string;

  @ApiProperty({ example: 'ivan@example.com' })
  @IsEmail()
  email!: string;

  @ApiPropertyOptional({ example: 22 })
  @IsOptional()
  @IsInt()
  @IsPositive()
  age?: number;
}
