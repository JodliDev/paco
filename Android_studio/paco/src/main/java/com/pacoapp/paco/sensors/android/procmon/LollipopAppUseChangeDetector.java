package com.pacoapp.paco.sensors.android.procmon;

import java.util.HashMap;
import java.util.List;

import com.google.common.collect.Lists;
import com.google.common.collect.Maps;

public class LollipopAppUseChangeDetector {

  private List<String> tasksOfInterestForClosing;
  private List<String> tasksOfInterestForOpening;
  private AppChangeListener listener;
  private HashMap<String, AppUsageEvent> lastOpenedMap;
  private HashMap<String, AppUsageEvent> lastClosedMap;

  public LollipopAppUseChangeDetector(List<String> tasksOfInterestForOpening,
                                      List<String> tasksOfInterestForClosing,
                                      AppChangeListener listener) {
    this.tasksOfInterestForOpening = tasksOfInterestForOpening != null
            ? tasksOfInterestForOpening
            : Lists.<String>newArrayList();
    this.tasksOfInterestForClosing = tasksOfInterestForClosing != null
            ? tasksOfInterestForClosing
            : Lists.<String>newArrayList();
    this.listener = listener;
    this.lastOpenedMap = Maps.newHashMap();
    this.lastClosedMap = Maps.newHashMap();
  }

  public void newEvents(List<AppUsageEvent> events) {
    if (events.size() == 0) {
      return;
    }

    Lists.reverse(events);
    for (AppUsageEvent appUsageEvent : events) {
      if (appUsageEvent.getType() == AppUsageEvent.MOVE_TO_BACKGROUND_EVENT) {
        AppUsageEvent lastClosedRecord = lastClosedMap.get(appUsageEvent.getAppIdentifier());
        if (lastClosedRecord == null || lastClosedRecord.getTimestamp() < appUsageEvent.getTimestamp()) {
          lastClosedMap.put(appUsageEvent.getAppIdentifier(), appUsageEvent);
          listener.appClosed(appUsageEvent, isAppOfInterestForClosing(appUsageEvent));
        }
      }
      else if (appUsageEvent.getType() == AppUsageEvent.MOVE_TO_FOREGROUND_EVENT) {
        AppUsageEvent lastOpenedRecord = lastOpenedMap.get(appUsageEvent.getAppIdentifier());
        if (lastOpenedRecord == null || lastOpenedRecord.getTimestamp() < appUsageEvent.getTimestamp()) {
          lastOpenedMap.put(appUsageEvent.getAppIdentifier(), appUsageEvent);
          listener.appOpened(appUsageEvent, isAppOfInterestForOpening(appUsageEvent));
        }

      }
    }
  }


  public boolean isAppOfInterestForClosing(AppUsageEvent appUsageEvent) {
    String app = appUsageEvent.getAppIdentifier();
    for(String s: tasksOfInterestForClosing){
      if(app.startsWith(s))
        return true;
    }
    return false;
  }

  public boolean isAppOfInterestForOpening(AppUsageEvent appUsageEvent) {
    String app = appUsageEvent.getAppIdentifier();
    for(String s: tasksOfInterestForOpening){
      if(app.startsWith(s)) {
        return true;
      }
    }
    return false;
  }


}
