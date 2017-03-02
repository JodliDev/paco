package com.pacoapp.paco.js.bridge;

import org.json.JSONException;
import org.junit.Assert;
import org.junit.Before;
import org.junit.Test;

public class JsUtilTest {

  @Before
  public void before() {

  }

  @Test
  public void testConvertJSONToPOJO_null() throws JSONException {
    SQLQuery q = JsUtil.convertJSONToPOJO(null);
    Assert.assertEquals(q, null);
  }

  @Test
  public void testConvertJSONToPOJO_partialValues_NoGroupByButHaving() {
    String inputString = "{query: {criteria: '(group_name =? and answer=?)',values:['New Group','bombay']},limit: 100, order: 'response_time' ,select: ['group_name','response_time', 'experiment_name','answer'], having: 'response_time>10'}";
    SQLQuery expectedValue = new SQLQuery();
    expectedValue.setProjection(new String[] { "group_name", "response_time", "experiment_name", "answer" });
    expectedValue.setCriteriaQuery("(group_name =? and answer=?)");
    expectedValue.setCriteriaValue(new String[] { "New Group", "bombay" });
    expectedValue.setLimit("100");
    expectedValue.setSortOrder("response_time");
    expectedValue.setGroupBy(null);
    expectedValue.setHaving(null);

    SQLQuery actualValue = null;
    try {
      actualValue = JsUtil.convertJSONToPOJO(inputString);
    } catch (JSONException e) {
      e.printStackTrace();
    }
    
    Assert.assertNotNull(actualValue);
    Assert.assertEquals(expectedValue.getProjection(), actualValue.getProjection());
    Assert.assertEquals(expectedValue.getCriteriaQuery().trim(), actualValue.getCriteriaQuery());
    Assert.assertEquals(expectedValue.getCriteriaValue(), actualValue.getCriteriaValue());
    Assert.assertEquals(expectedValue.getSortOrder().trim(), actualValue.getSortOrder());
    Assert.assertEquals(expectedValue.getLimit().trim(), actualValue.getLimit());
    Assert.assertEquals(expectedValue.getCriteriaQuery().trim(), actualValue.getCriteriaQuery());
    Assert.assertNull(actualValue.getGroupBy());
    Assert.assertNull(actualValue.getHaving());
  }
  @Test
   public void testConvertJSONToPOJO_allValues(){
     String inputString = "{query: {criteria: '(group_name =? and answer=?)',values:['New Group','bombay']},limit: 100, order: 'response_time' ,select: ['group_name','response_time', 'experiment_name','answer'], group: 'response_time', having: 'response_time>5'}";
     SQLQuery expectedValue = new SQLQuery();
     expectedValue.setProjection(new String[] { "group_name", "response_time", "experiment_name", "answer" });
     expectedValue.setCriteriaQuery("(group_name =? and answer=?)");
     expectedValue.setCriteriaValue(new String[] { "New Group", "bombay" });
     expectedValue.setLimit("100");
     expectedValue.setSortOrder("response_time");
     expectedValue.setGroupBy("response_time");
     expectedValue.setHaving("response_time>5");

     SQLQuery actualValue = null;
     try {
       actualValue = JsUtil.convertJSONToPOJO(inputString);
     } catch (JSONException e) {
       e.printStackTrace();
     }
     
     Assert.assertNotNull(actualValue);
     Assert.assertEquals(expectedValue.getProjection(), actualValue.getProjection());
     Assert.assertEquals(expectedValue.getCriteriaQuery().trim(), actualValue.getCriteriaQuery());
     Assert.assertEquals(expectedValue.getCriteriaValue(), actualValue.getCriteriaValue());
     Assert.assertEquals(expectedValue.getSortOrder().trim(), actualValue.getSortOrder());
     Assert.assertEquals(expectedValue.getLimit().trim(), actualValue.getLimit());
     Assert.assertEquals(expectedValue.getCriteriaQuery().trim(), actualValue.getCriteriaQuery());
     Assert.assertEquals(expectedValue.getGroupBy(),actualValue.getGroupBy());
     Assert.assertEquals(expectedValue.getHaving(),actualValue.getHaving());
   }
  
   @Test
   public void testConvertJSONToPOJO_partialValues_NoSortOrderButLimit(){
     String inputString = "{query: {criteria: '(group_name =? and answer=?)',values:['New Group','bombay']},limit: 100, select: ['group_name','response_time', 'experiment_name','answer'], limit: '10'}";
     SQLQuery expectedValue = new SQLQuery();
     expectedValue.setProjection(new String[] { "group_name", "response_time", "experiment_name", "answer" });
     expectedValue.setCriteriaQuery("(group_name =? and answer=?)");
     expectedValue.setCriteriaValue(new String[] { "New Group", "bombay" });
     expectedValue.setLimit(null);
     expectedValue.setSortOrder(null);
     expectedValue.setGroupBy(null);
     expectedValue.setHaving(null);

     SQLQuery actualValue = null;
     try {
       actualValue = JsUtil.convertJSONToPOJO(inputString);
     } catch (JSONException e) {
       e.printStackTrace();
     }
     
     Assert.assertNotNull(actualValue);
     Assert.assertEquals(expectedValue.getProjection(), actualValue.getProjection());
     Assert.assertEquals(expectedValue.getCriteriaQuery().trim(), actualValue.getCriteriaQuery());
     Assert.assertEquals(expectedValue.getCriteriaValue(), actualValue.getCriteriaValue());
     Assert.assertNull(actualValue.getSortOrder());
     Assert.assertNull(actualValue.getLimit());
     Assert.assertEquals(expectedValue.getCriteriaQuery().trim(), actualValue.getCriteriaQuery());
     Assert.assertNull(actualValue.getGroupBy());
     Assert.assertNull(actualValue.getHaving());

   }
}
