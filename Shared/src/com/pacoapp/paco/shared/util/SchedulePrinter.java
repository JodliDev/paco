package com.pacoapp.paco.shared.util;

import java.util.List;
import java.util.ResourceBundle;

import org.joda.time.DateMidnight;
import org.joda.time.DateTime;

import com.google.common.base.Joiner;
import com.google.common.collect.Lists;
import com.pacoapp.paco.shared.model2.ActionTrigger;
import com.pacoapp.paco.shared.model2.ExperimentDAO;
import com.pacoapp.paco.shared.model2.ExperimentGroup;
import com.pacoapp.paco.shared.model2.Schedule;
import com.pacoapp.paco.shared.model2.ScheduleTrigger;
import com.pacoapp.paco.shared.model2.SignalTime;

public class SchedulePrinter {
  private static ResourceBundle locale = ResourceBundle.getBundle("locale.Strings");;
  public static final String[] DAYS_SHORT_NAMES = new String[] {
          locale.getString("Sun"),
          locale.getString("Mon"),
          locale.getString("Tue"),
          locale.getString("Wed"),
          locale.getString("Thu"),
          locale.getString("Fri"),
          locale.getString("Sat")
  };
//  public static final String[] DAYS_SHORT_NAMES = new String[] { "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" };

  public static String createStringOfAllSchedules(ExperimentDAO experiment) {
    List<String> groupStrings = Lists.newArrayList();
    
    List<ExperimentGroup> groups = experiment.getGroups();    
    for (ExperimentGroup experimentGroup : groups) {      
      List<String> triggerStrings = Lists.newArrayList();
      List<ActionTrigger> actionTriggers = experimentGroup.getActionTriggers();
      for (ActionTrigger actionTrigger : actionTriggers) {
        if (actionTrigger instanceof ScheduleTrigger) {          
          ScheduleTrigger scheduleTrigger = (ScheduleTrigger)actionTrigger;
          
          List<String> scheduleStrings = Lists.newArrayList();
          List<Schedule> schedules = scheduleTrigger.getSchedules();
          for (Schedule schedule : schedules) {
            scheduleStrings.add(SchedulePrinter.toString(schedule));
          }
          String concatenatedStrings = Joiner.on(", ").skipNulls().join(scheduleStrings);          
          triggerStrings.add(scheduleTrigger.getId() +  
                                                ":(" + 
                  concatenatedStrings + 
                  ")");
        }
      }
      String concatenatedTriggers = Joiner.on(" | ").skipNulls().join(triggerStrings);
      groupStrings.add(experimentGroup.getName() + 
                       ":[" +
              concatenatedTriggers + "]");         
    }
    return Joiner.on(", ").skipNulls().join(groupStrings);
  }

  
  public static String toString(Schedule schedule) {
    return buildString(schedule, true);
  }


  private static String buildString(Schedule schedule, boolean includeIds) {
    StringBuilder buf = new StringBuilder();
    if (includeIds) {
      buf.append(schedule.getId());
      buf.append(":");
    }
    if (schedule.getScheduleType().equals(Schedule.WEEKDAY)
            || schedule.getScheduleType().equals(Schedule.DAILY)) {
      dailyScheduleToString(buf, schedule);
    } else if (schedule.getScheduleType().equals(Schedule.WEEKLY)) {
      weeklyScheduleToString(buf, schedule);
    } else if (schedule.getScheduleType().equals(Schedule.MONTHLY)) {
      monthlyScheduleToString(buf, schedule);
    } else if (schedule.getScheduleType().equals(Schedule.ESM)) {
      esmScheduleToString(buf, schedule);
    }
    return buf.toString();
  }
  
  public static String toPrettyString(Schedule schedule) {
    return buildString(schedule, false);
  }


  private static void dailyScheduleToString(StringBuilder buf, Schedule schedule) {
    final Integer repeatRate = schedule.getRepeatRate();
    if (repeatRate > 1) {
      buf.append(locale.getString("Every")).append(" ");
      buf.append(repeatRate);
      buf.append(" ").append(locale.getString("days_at")).append(" ");
    } else {
      buf.append(locale.getString("Daily_at")).append(" ");
    }
    timesOfDayToString(buf, schedule);
  }

  public static void timesOfDayToString(StringBuilder buf, Schedule schedule) {
    boolean firstTime = true;
    if (schedule.getSignalTimes() != null) {
      for (SignalTime time : schedule.getSignalTimes()) {
        if (firstTime) {
          firstTime = false;
        } else {
          buf.append(",");
        }
        buf.append(getHourOffsetAsTimeString(time));
        final String label = time.getLabel();
        if (label != null && !label.isEmpty() && !label.equals("null")) {
          buf.append(" (").append(label).append(")");
        }
      }
    }

  }

  private static void monthlyScheduleToString(StringBuilder buf, Schedule schedule) {
    final Integer repeatRate = schedule.getRepeatRate();
    if (repeatRate > 1) {
      buf.append(locale.getString("Every")).append(" ");
      buf.append(repeatRate);
      buf.append(" ").append(locale.getString("months_on")).append(" ");
    } else {
      buf.append(" ").append(locale.getString("Monthly_on")).append(" ");
    }
    if (schedule.getByDayOfMonth()) {
      buf.append(schedule.getNthOfMonth());
      buf.append(stringNamesOf(schedule.getWeekDaysScheduled()));
    } else {
      buf.append(schedule.getDayOfMonth());
    }
    buf.append(" at ");
    timesOfDayToString(buf, schedule);
  }

  private static void weeklyScheduleToString(StringBuilder buf, Schedule schedule) {
    final Integer repeatRate = schedule.getRepeatRate();
    if (repeatRate > 1) {
      buf.append(locale.getString("Every")).append(" ");
      buf.append(repeatRate);
      buf.append(locale.getString("weeks_at"));
    } else {
      buf.append(locale.getString("Weekly_at")).append(" ");
    }
    buf.append(stringNamesOf(schedule.getWeekDaysScheduled()));
    buf.append(" ").append(locale.getString("at")).append(" ");
    timesOfDayToString(buf, schedule);
  }

  private static void esmScheduleToString(StringBuilder buf, Schedule schedule) {
    buf.append(locale.getString("randomly")).append(" ")
        .append(schedule.getEsmFrequency().toString())
        .append(" ").append(locale.getString("times_per")).append(" ")
        .append(Schedule.ESM_PERIODS_NAMES[schedule.getEsmPeriodInDays()])
        .append(" ").append(locale.getString("between")).append(" ")
        .append(getHourOffsetAsTimeString(schedule.getEsmStartHour()))
        .append(" ").append(locale.getString("and")).append(" ")
        .append(getHourOffsetAsTimeString(schedule.getEsmEndHour()));
    if (schedule.getEsmWeekends()) {
      buf.append(" ").append(locale.getString("incl_weekends")).append(" ");
    }

  }

  public static String getHourOffsetAsTimeString(Long time) {
    DateTime endHour = new DateMidnight().toDateTime().plus(time);
    return TimeUtil.hourFormatter.print(endHour);
  }

  public static String getHourOffsetAsTimeString(SignalTime time) {
    return getHourOffsetAsTimeString((long)time.getFixedTimeMillisFromMidnight());
  }



  private static void appendKeyValue(StringBuilder buf, String key, String value) {
    buf.append(key);
    buf.append(" = ");
    buf.append(value);
  }

  private static void comma(StringBuilder buf) {
    buf.append(",");
  }

  private static String stringNamesOf(Integer weekDaysScheduled) {
    StringBuilder buf = new StringBuilder();
    boolean first = true;
    for (int i= 0; i < Schedule.DAYS_OF_WEEK.length;i++) {
      if ((weekDaysScheduled & Schedule.DAYS_OF_WEEK[i]) == Schedule.DAYS_OF_WEEK[i]) {
        if (first) {
          first = false;
        } else {
          comma(buf);
        }
        buf.append(DAYS_SHORT_NAMES[i]);
      }
    }
    return buf.toString();
  }



}
