pacoApp.factory('experimentsVizService', ['$http', 'experimentService', '$filter', function ($http, experimentService, $filter) {

  var experiment = '';

  function getExperiment(id) {
    var getExperiment = experimentService.getExperiment(id).then(function successCallback(experimentData) {
      return experimentData.data;
    }, function errorCallback(error) {
      return error;
    });
    experiment = getExperiment.then(function successCallback(experiment) {
      return experiment;
    });
    return experiment;
  }

  function getParticipants(experimentId) {
    if (experimentId != undefined) {
      var message = '{ "select":["who"], "query" : { "criteria" : "experiment_id = ?", "values" : [' + experimentId + ']},"group":"who"}';
      var participants = httpPostBody(message);
    }
    return participants;
  }

  function getAllTexts(experimentId,texts){
    var questionMarks = [];
    var textsList = [];

    for (var i = 0; i < texts.length; i++) {
      questionMarks.push("?");
      textsList.push('"' + texts[i] + '"');
    }

    if(experimentId !== undefined && textsList !== undefined){
      var distinctTextQuery = '{ "select":["group_name","text"], "query" : { "criteria" : "experiment_id = ? and text not in (' + questionMarks + ')", "values" : ['+experimentId+',' + textsList + ']},"order":"group_name,text","group":"group_name,text"}';
      console.log(distinctTextQuery);
      var textQuery = httpPostBody(distinctTextQuery);
    }
    return textQuery;
  }

  function getStartDate(experimentId){
    var startDateQuery = "";
    if(experimentId !== undefined){
      startDateQuery = '{ "select":["response_time"], "query" : { "criteria" : "experiment_id = ? and response_time is not null", "values" : [' + experimentId + ']},"order":"response_time asc","limit":"1"}';
    }
    var startDate = httpPostBody(startDateQuery);
    return startDate;
  }

  function getEndDate(experimentId){
    var endDateQuery = "";
    if(experimentId !== undefined){
      endDateQuery = '{ "select":["response_time"], "query" : { "criteria" : "experiment_id = ? and response_time is not null", "values" : [' + experimentId + ']},"order":"response_time desc","limit":"1"}';
    }

    var endDate = httpPostBody(endDateQuery);
    return endDate;
  }

  function getEventsCounts(id) {
    var eventsCount = $http({
      method: 'GET',
      url: '/participantStats?experimentId=' + id + '&reportType=totalEventCounts&statv2=1'
    }).then(function successCallback(response) {
      return response;
    }, function errorCallback(error) {
      return error;
    });
    return eventsCount;
  }

  function getEvents(experimentId, groups, texts, participants, startDateTime, endDateTime) {
    var message = "";
    var expTexts = {};
    var expGroups = {};
    var expParticipants = {};

    if (experimentId != undefined && groups != undefined) {
      expTexts = parametersList(texts,"texts");
      expGroups = parametersList(groups,"groups");
      message = '{"select":["who","when","response_time","text","answer","client_timezone"], "query" : { "criteria" : "experiment_id = ? and group_name in(' + expGroups.questionMarks + ') and text in (' + expTexts.questionMarks + ')", "values" : [' + experimentId + ', "' + expGroups.params + '","' + expTexts.params + '"]},"order":"who","order":"text"}';
    }
    if (experimentId != undefined && participants != undefined && participants.length > 0 && texts != undefined) {
      expGroups = parametersList(groups, "groups");
      expTexts  = parametersList(texts, "texts");
      expParticipants = parametersList(participants, "participants");
      message = '{ "select":["who","when","response_time","text","answer","client_timezone"], "query" : { "criteria" : "experiment_id = ? and group_name in (' + expGroups.questionMarks + ') and text in (' + expTexts.questionMarks + ') and who in (' + expParticipants.questionMarks + ')", "values" : [' + experimentId + ',  '+ expGroups.params + ' ,' + expTexts.params + ' ,' + expParticipants.params + ']},"order":"who","order":"text"}';
    }
    //filter data based on start and end date/timestamp values
    if (experimentId != undefined && participants != undefined && participants.length > 0 && texts != undefined && groups != undefined && startDateTime != undefined && endDateTime != undefined) {
      expGroups = parametersList(groups, "groups");
      expTexts  = parametersList(texts, "texts");
      expParticipants = parametersList(participants, "participants");
      message = '{ "select":["who","when","response_time","text","answer","client_timezone"], "query" : { "criteria" : "experiment_id = ? and response_time>? and response_time<? and group_name in (' + expGroups.questionMarks + ') and text in (' + expTexts.questionMarks + ') and who in (' + expParticipants.questionMarks + ')", "values" : [' + experimentId + ', "' + startDateTime + '", "' + endDateTime + '",' + expGroups.params + ' ,' + expTexts.params + ' ,' + expParticipants.params + ']},"order":"who,text"}';
    }
    console.log(message);
    var events = httpPostBody(message);
    return events;
  }

  function parametersList(parameterList, parameter){
    var questionMarks_list = [];
    var paramsList = [];

    for (var i = 0; i < parameterList.length; i++) {
      questionMarks_list.push("?");
      paramsList.push('"' + parameterList[i] + '"');
    }
    return {"questionMarks":questionMarks_list,"params":paramsList }
  }

  function httpPostBody(message) {
    var response = $http({
      method: 'POST',
      url: '/csSearch',
      data: angular.fromJson(message),
    }).then(function successCallback(response) {
      return response;
    }, function errorCallback(error) {
      return error;
    });
    return response;
  }

  function saveVisualizations(experiment) {
    var saveVizs = $http.post('/experiments?id=' + experiment.id, JSON.stringify(experiment))
        .then(function successCallback(response) {
          return response;
        }, function errorCallback(error) {
          console.log(error);
        });
    return saveVizs;
  }

  return {
    getExperiment: getExperiment,
    getEvents: getEvents,
    getParticipants: getParticipants,
    getAllTexts:getAllTexts,
    getEventsCounts: getEventsCounts,
    getStartDate:getStartDate,
    getEndDate:getEndDate,
    saveVisualizations: saveVisualizations
  }
}]);
