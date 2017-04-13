package com.pacoapp.paco.shared.comm;

public class Outcome implements java.io.Serializable {

  private long eventId;
  private boolean status;
  private String errorMessage;
  private boolean isServerMessage;
  private long msgTimestamp;

  public Outcome(long eventId) {
    this();
    this.eventId = eventId;
  }

  public Outcome() {
    super();
      this.status = false;//always assume that data was not saved unless server tells us otherwise
  }

  public Outcome(long eventId, String errorMessage) {
    this(eventId);
    this.status = false;
    this.errorMessage = errorMessage;
  }

  public long getEventId() {
    return eventId;
  }

  public void setEventId(long eventId) {
    this.eventId = eventId;
  }

  public boolean succeeded() {
    return status;
  }

  public void setStatus(boolean status) {
    this.status = status;
  }


  public String getErrorMessage() {
    return errorMessage;
  }

  public void setErrorMessage(String errorMessage) {
    this.errorMessage = errorMessage;
  }

  public void setError(String errorMessage) {
    this.status = false;
    this.errorMessage = errorMessage;

  }

  public boolean getStatus() {
    return status;
  }

  public void setIsServerMessage(boolean isServerMessage) {
    this.status = false;
    this.isServerMessage = isServerMessage;
  }
  public boolean getIsServerMessage() {
    return this.isServerMessage;
  }
  public void setMsgTimestamp(long msgTimestamp) {
    this.msgTimestamp = msgTimestamp;
  }
  public long getMsgTimestamp() {
    return this.msgTimestamp;
  }
}
