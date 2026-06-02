import { ApiProperty } from '@nestjs/swagger';
import { IsEmail, IsIn, IsString, MinLength } from 'class-validator';

export class CreateAppUserDto {
  @ApiProperty()
  @IsEmail()
  email!: string;

  @ApiProperty()
  @IsString()
  @MinLength(4)
  password!: string;

  @ApiProperty()
  @IsString()
  @MinLength(2)
  fullName!: string;

  @ApiProperty({ enum: ['admin', 'guest'] })
  @IsIn(['admin', 'guest'])
  roleCode!: string;
}
