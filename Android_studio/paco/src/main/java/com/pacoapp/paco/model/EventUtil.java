package com.pacoapp.paco.model;

import org.joda.time.DateTime;

public class EventUtil {

  public static Event createEvent(Experiment experiment, String experimentGroup, Long actionTriggerId,
                                  Long actionId, Long actionTriggerSpecId, Long scheduledTime) {
    Event event = new Event(experiment);
    if (scheduledTime != null && scheduledTime != 0L) {
      event.setScheduledTime(new DateTime(scheduledTime));
    }
    event.setExperimentGroupName(experimentGroup);
    event.setActionId(actionId);
    event.setActionTriggerId(actionTriggerId);
    event.setActionTriggerSpecId(actionTriggerSpecId);

    return event;
  }

  public static Event createSitesVisitedPacoEvent(String usedAppsString, Experiment experiment, long startTime) {
    Event event = new Event(experiment);

    Output responseForInput = new Output();

    responseForInput.setAnswer(usedAppsString);
    responseForInput.setName("sites_visited");
    event.addResponse(responseForInput);

    Output responseForInputSessionDuration = new Output();
    long sessionDuration = (System.currentTimeMillis() - startTime) / 1000;
    responseForInputSessionDuration.setAnswer(Long.toString(sessionDuration));
    responseForInputSessionDuration.setName("session_duration");

    event.addResponse(responseForInputSessionDuration);
    return event;
  }

}
