GO
/****** Object:  UserDefinedFunction [dbo].[pl_fTimeToPlanTime]    
Script from medialog v7.2 
конвертирует врем€ из SQL вида в числовое представление медиалога
******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[pl_fTimeToPlanTime]( @DTime DateTime ) 
RETURNS Int
AS
BEGIN
DECLARE
  @PlanTime Int
  SET @PlanTime = DatePart(hour, @DTime )*100+  DatePart(minute, @DTime );
  RETURN(@PlanTime)
END

GO
/****** Object:  UserDefinedFunction [dbo].[pl_fPlanTimeToTime]    
Script from medialog v7.2 
конвертирует врем€ из числового представлени€ медиалога в SQL вида
******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[pl_fPlanTimeToTime]( @PlanTime int ) 
RETURNS DateTime
AS
BEGIN
DECLARE
 @Hours Int,
 @Minutes Int,
 @RealTime DateTime
  SET @Hours = @PlanTime / 100;
  SET @Minutes = @PlanTime % 100;
  SET @RealTime = DateAdd( Hour, @Hours, 0  ) +  DateAdd( minute, @Minutes,0  );
  RETURN(@RealTime)
END

GO
/****** Object:  UserDefinedFunction [dbo].[pl_GetSubjAgenda]    
Script from medialog v7.2
получение pl_agend_id дл€ нужной даты по номеру расписани€ 
******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[pl_GetSubjAgenda]( @SubjID Int, @CurDate DateTime )  
RETURNS INT AS  
BEGIN 
DECLARE
  @LinkMonday DateTime,
  @CurrentMonday DateTime,
  @WeeksQty Int,
  @Res Int,
  @S1 Int,
  @S2 Int, 
  @S3 Int, 
  @S4 Int,
  @i Int

--  SELECT @LinkMonday = 
  select @WeeksQty = semaines, @S1 = pl_agend_id1, @S2 = pl_agend_id2, @S3 = pl_agend_id3, @S4 = pl_agend_id4, @LinkMonday=start_from  from pl_subj
  where PL_SUBJ_ID = @SubjID;

  if (@WeeksQty = 0) 
  begin
     SET @Res = @S1;     
  end
  else
  begin
--    SET @LinkMonday = @PlanDate;
    SET @CurrentMonday = @CurDate;
    SET @LinkMonday = @LinkMonday - DatePart(weekday, @LinkMonday-1) + 1;
    SET @CurrentMonday = @CurrentMonday - DatePart(weekday, @CurrentMonday-1) + 1;
    SET @i = (  DateDiff( Day, @CurrentMonday, @LinkMonday ) / 7) % (@WeeksQty+1);
    if @i<0 
      SET @i = @WeeksQty+@i+1;
    if @i = 0
      SET @Res = @S1;     
    if @i = 1
      SET @Res = @S2;     
    if @i = 2
      SET @Res = @S3;     
    if @i = 3
      SET @Res = @S4;     
  end;
  Return(@Res);
END

GO
/****** Object:  UserDefinedFunction [dbo].[pl_GetPlDay]    
Script from medialog v7.2 
получение PL_DAY_ID дневной сетки дл€ нужной даты по AgendaID сетки расписани€ и PlanSubjID расписани€???
зачем нужен PlanSubjID???
как обрабатывать PL_DAY.ENABLED=0, сетка не активна - значит день пустой вообще!
интервалы не сделаны день i-той недели не сделан
******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[pl_GetPlDay] (@AgendaID Int, @DatePlan DateTime, @PlanSubjID int )  
RETURNS Integer AS  
BEGIN 
  DECLARE 
    @Res int,
    @DayOfWeek int,
    @DayOfMonth int,
    @DayMonth int,
    @DayId int, 
    @PlSubj int,
    @DayYear int,
    @DayWeek int,
    @DayEven int,
    @DayWeekMonth int,
    @IntWork int,
    @IntOff int,
    @IntStartFrom datetime,
    @PeriodFrom datetime,
    @PeriodTo datetime,
    @n0 int,
    @n1 int,
    @D int
    --курсор по дн€м расписани€
    DECLARE CURDAYS CURSOR SCROLL FOR                                      
      SELECT PL_DAY_ID, DAY_OF_WEEK, PL_SUBJ_ID, DAY_OF_MONTH, DAY_MONTH, DAY_YEAR, DAY_WEEK, DAY_EVEN, 
                     DAY_WEEK_MONTH, INTERVAL_WORK, INTERVAL_OFF, INTERVAL_STARTFROM, PERIOD_FROM, PERIOD_TO
    FROM PL_DAY
      WHERE                                                           
        PL_AGEND_ID = @AgendaID
    ORDER BY DAY_ORDER
    OPEN CURDAYS   

    SET @Res = 0;

    FETCH FIRST FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
    WHILE @@FETCH_STATUS = 0                                     
    BEGIN                                                        
      --€®ютх®ър фэ† эхфхыш
      if @DayOfWeek <>  DatePart(weekday, @DatePlan-1) and ( @DayOfWeek IS NOT NULL ) and ( @DayOfWeek <> 0 ) 
      BEGIN
         FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
         CONTINUE;
      END;
      -- €®ютх®ър PL_SUBJ
      if ( @PlanSubjID <> @PlSubj ) and ( @PlSubj IS NOT NULL )
      BEGIN
         FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
         CONTINUE;
      END;
      -- €®ютх®ър DAY_OF_MONTH
      if (@DayOfMonth <> DATEPART( d, @DatePlan )) and ( @DayOfMonth IS NOT NULL ) and ( @DayOfMonth <> 0 ) 
      BEGIN
         FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
         CONTINUE;
      END;
      -- €®ютх®ър MONTH
      if ( @DayMonth <> DATEPART( month, @DatePlan )) and ( @DayMonth IS NOT NULL )  and ( @DayMonth <> 0 ) 
      BEGIN
         FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
         CONTINUE;
      END;
      -- €®ютх®ър YEAR
      if ( @DayYear <> DATEPART( year, @DatePlan )-1995 ) and ( @DayYear IS NOT NULL )  and ( @DayYear <> 0 ) 
      BEGIN
         FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
         CONTINUE;
      END;
      -- €®ютх®ър DAYWEEK - эхфхы† т ьхЄ†°х
      if ( @DayWeek <> dbo.pl_GetNumWeekInMonth( @DatePlan )) and ( @DayWeek IS NOT NULL )  and ( @DayWeek <> 0 ) 
      BEGIN
         FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
         CONTINUE;
      END;

      -- проверка DAYEVEN - четные-нечетные дни
      if ( @DayEven IS NOT NULL )  and ( @DayEven <> 0 ) 
      BEGIN
         if ( @DayEven = 1 )  and ( ( DATEPART( day, @DatePlan) % 2 )>=1)
         BEGIN
           FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
           CONTINUE;
         END;
         if ( @DayEven = 2 )  and ( ( DATEPART( day, @DatePlan) % 2 )<1)
         BEGIN
           FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
           CONTINUE;
         END
      END;
  
      -- проверка на период - нижн€€ граница
      if ( @PeriodFrom IS NOT NULL )  and ( @PeriodFrom <> 0 ) 
      BEGIN
          if @DatePlan < @PeriodFrom 
          BEGIN
            FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
            CONTINUE;
          END;    
      END;
      -- проверка на период - верхн€€ граница
      if ( @PeriodTo IS NOT NULL )  and ( @PeriodTo <> 0 ) 
      BEGIN
          if @DatePlan > @PeriodTo 
          BEGIN
            FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
            CONTINUE;
          END;    
      END;

     -- интервальный тип - осталось сделать

     -- первый понедельник мес€ца - осталось сделать
      -- проверка DAYWEEK - недел€ в мес€це
      if ( @DayOfWeek IS NOT NULL ) and  ( @DayOfWeek <> 0) and ( @DayWeekMonth IS NOT NULL )  and ( @DayWeekMonth <> 0 ) 
      BEGIN
     
          set @n0 = DatePart(weekday, @DatePlan - DATEPART(day, @DatePlan) );

          if ( @n0<= @DayOfWeek) 
            set @n1= @DayOfWeek - @n0 +1
          else
            set @n1= 8 + @DayOfWeek - @n0;
 
          set @D = DatePart( day, @DatePlan )

         if @D<>(@n1 + 7*(@DayWeekMonth -1 )) 
         begin
           FETCH NEXT FROM CURDAYS INTO @DayId, @DayOfWeek, @PlSubj, @DayOfMonth, @DayMonth, @DayYear, @DayWeek, @DayEven, @DayWeekMonth, @IntWork, @IntOff, @IntStartFrom, @PeriodFrom, @PeriodTo
           CONTINUE;
         end;
      END;
      SET @Res = @DayID;
      RETURN( @Res );
    END;

  RETURN( @Res );
/*
--  SELECT @LinkMonday = 
  select @WeeksQty = semaines, @S1 = pl_agend_id1, @S2 = pl_agend_id2, @S3 = pl_agend_id3, @S4 = pl_agend_id4, @LinkMonday=start_from  from pl_subj
  where PL_SUBJ_ID = @SubjID;
	------
	SET DATEFIRST 1
	 declare @date datetime
	 set @date=@DatePlan

	 SELECT 
	 PL_DAY_ID 
	 FROM pl_day 
	 WHERE 
	 PL_DAY.ENABLED=1 
	 --and pl_agend_id in (437) 
	 and (PERIOD_FROM is null or PERIOD_FROM <= @date)						/* ƒень активен с */
	 and (PERIOD_TO is null or PERIOD_TO >= @date)							/* ƒень активен по */
	 and (isnull(DAY_MONTH,0)=0 or DAY_MONTH=month(@date))							/* ћес€ц */
	 and (isnull(DAY_MONTH,0)=0 or DAY_MONTH=month(@date))
	 and (isnull(DAY_OF_MONTH,0)=0 or DAY_OF_MONTH = day(@date))					/* ƒень мес€ца */
	 and (isnull(DAY_YEAR,0)=0 or DAY_YEAR = (year(@date)-1995)/* magic year*/)		/* √од */
	 and (isnull(DAY_EVEN,0)=0 or DAY_EVEN=(day(@date)& 1 + 1))						/* ѕризнак четности, нечетный 2, четный 1 */
	 and (isnull(DAY_WEEK,0)=0 or DAY_WEEK=(
		 DATEPART(week,@date)-DATEPART(week,DATEADD(day,1-day(@date),@date))+1))		/* Ќомер недели в мес€це */
	 and (isnull(DAY_OF_WEEK,0)=0 
			or (DAY_OF_WEEK=DATEPART(weekday,@date) and isnull(DAY_WEEK_MONTH,0)=0)	/* Ќомер дн€ в неделе */
			or (DAY_OF_WEEK=DATEPART(weekday,@date) and DAY_WEEK_MONTH=DATEDIFF(day,DATEADD(day,1-day(@date),@date),@date)/7+1))
					/* i-тый день недели в мес€це */
	 and (case when(isnull(INTERVAL_WORK,0)=0 and isnull(INTERVAL_OFF,0)=0) then 1 else (
			case when (DATEDIFF(day,INTERVAL_STARTFROM,@date))%(INTERVAL_WORK+INTERVAL_OFF)<=(INTERVAL_WORK-1)then 1 else 0 end
		 ) end)=1
	 ORDER BY pl_agend_id,day_order
------ 
*/
END

GO
/****** Object:  StoredProcedure [dbo].[pl_GetMedecinGrid]    
Script from medialog v7.2
получение целой таблицы расписани€ на дату по номеру расписани€
 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROCEDURE [dbo].[pl_GetMedecinGrid]( @SubjID int, @DatePlan datetime, @ShowCancelled bit )  AS
BEGIN
DECLARE
  @SubjAgenda int,
  @PlDay int
  set @SubjAgenda = dbo.pl_GetSubjAgenda( @SubjID, @DatePlan );
  set @PlDay = dbo.pl_GetPlDay( @SubjAgenda, @DatePlan, @SubjID );

  if (@ShowCancelled=0)
  begin
    (
      select @DatePlan+dbo.pl_fPlanTimeToTime(INT_FROM) as StartTime, @DatePlan+dbo.pl_fPlanTimeToTime(INT_TO) as EndTime, 2 EventType, PL_INT_ID RecID  from PL_INT where PL_DAY_ID = @PlDay
    )
    union all
    (
      select @DatePlan+dbo.pl_fPlanTimeToTime(Heure) as StartTime, @DatePlan+dbo.pl_fPlanTimeToTime(Heure) + dbo.pl_fPlanTimeToTime(Duree) as EndTime, 1 EventType, PLANNING_ID RecID from planning
        where
        pl_subj_id = @SubjID
        and DATE_CONS = @DatePlan
        and ((CANCELLED = 0) or (CANCELLED IS NULL))
        and ((STATUS = 0) or (STATUS IS NULL))
    )
    union all
    (
      select From_date + dbo.pl_fPlanTimeToTime( From_Time ) StartTime, To_date + dbo.pl_fPlanTimeToTime( To_Time ) EndTime, 3 EventType, PL_EXCL_ID RecID from pl_excl where
      OWNER_TYPE = 2
      and FROM_DATE<=  @DatePlan
      and TO_DATE >= @DatePlan
      and PL_SUBJ_ID = @SubjID
    )
  end
  else
  begin
    (
      select @DatePlan+dbo.pl_fPlanTimeToTime(INT_FROM) as StartTime, @DatePlan+dbo.pl_fPlanTimeToTime(INT_TO) as EndTime, 2 EventType, PL_INT_ID RecID  from PL_INT where PL_DAY_ID = @PlDay
    )
    union all
    (
      select @DatePlan+dbo.pl_fPlanTimeToTime(Heure) as StartTime, @DatePlan+dbo.pl_fPlanTimeToTime(Heure) + dbo.pl_fPlanTimeToTime(Duree) as EndTime, 1 EventType, PLANNING_ID RecID from planning
        where
        pl_subj_id = @SubjID
        and DATE_CONS = @DatePlan
        and ((STATUS = 0) or (STATUS IS NULL))
    )
    union all
    (
      select From_date + dbo.pl_fPlanTimeToTime( From_Time ) StartTime, To_date + dbo.pl_fPlanTimeToTime( To_Time ) EndTime, 3 EventType, PL_EXCL_ID RecID from pl_excl where
      OWNER_TYPE = 2
      and FROM_DATE<=  @DatePlan
      and TO_DATE >= @DatePlan
      and PL_SUBJ_ID = @SubjID
    )
  end
END
