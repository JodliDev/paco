//
//  Generated by the J2ObjC translator.  DO NOT EDIT!
//  source: joda-time/src/main/java/org/joda/time/chrono/BasicMonthOfYearDateTimeField.java
//

#include "J2ObjC_header.h"

#pragma push_macro("OrgJodaTimeChronoBasicMonthOfYearDateTimeField_INCLUDE_ALL")
#ifdef OrgJodaTimeChronoBasicMonthOfYearDateTimeField_RESTRICT
#define OrgJodaTimeChronoBasicMonthOfYearDateTimeField_INCLUDE_ALL 0
#else
#define OrgJodaTimeChronoBasicMonthOfYearDateTimeField_INCLUDE_ALL 1
#endif
#undef OrgJodaTimeChronoBasicMonthOfYearDateTimeField_RESTRICT

#if !defined (OrgJodaTimeChronoBasicMonthOfYearDateTimeField_) && (OrgJodaTimeChronoBasicMonthOfYearDateTimeField_INCLUDE_ALL || defined(OrgJodaTimeChronoBasicMonthOfYearDateTimeField_INCLUDE))
#define OrgJodaTimeChronoBasicMonthOfYearDateTimeField_

#define OrgJodaTimeFieldImpreciseDateTimeField_RESTRICT 1
#define OrgJodaTimeFieldImpreciseDateTimeField_INCLUDE 1
#include "org/joda/time/field/ImpreciseDateTimeField.h"

@class IOSIntArray;
@class OrgJodaTimeChronoBasicChronology;
@class OrgJodaTimeDurationField;
@protocol OrgJodaTimeReadablePartial;

@interface OrgJodaTimeChronoBasicMonthOfYearDateTimeField : OrgJodaTimeFieldImpreciseDateTimeField

#pragma mark Public

- (jlong)addWithLong:(jlong)instant
             withInt:(jint)months;

- (jlong)addWithLong:(jlong)instant
            withLong:(jlong)months;

- (IOSIntArray *)addWithOrgJodaTimeReadablePartial:(id<OrgJodaTimeReadablePartial>)partial
                                           withInt:(jint)fieldIndex
                                      withIntArray:(IOSIntArray *)values
                                           withInt:(jint)valueToAdd;

- (jlong)addWrapFieldWithLong:(jlong)instant
                      withInt:(jint)months;

- (jint)getWithLong:(jlong)instant;

- (jlong)getDifferenceAsLongWithLong:(jlong)minuendInstant
                            withLong:(jlong)subtrahendInstant;

- (jint)getLeapAmountWithLong:(jlong)instant;

- (OrgJodaTimeDurationField *)getLeapDurationField;

- (jint)getMaximumValue;

- (jint)getMinimumValue;

- (OrgJodaTimeDurationField *)getRangeDurationField;

- (jboolean)isLeapWithLong:(jlong)instant;

- (jboolean)isLenient;

- (jlong)remainderWithLong:(jlong)instant;

- (jlong)roundFloorWithLong:(jlong)instant;

- (jlong)setWithLong:(jlong)instant
             withInt:(jint)month;

#pragma mark Package-Private

- (instancetype)initWithOrgJodaTimeChronoBasicChronology:(OrgJodaTimeChronoBasicChronology *)chronology
                                                 withInt:(jint)leapMonth;

@end

J2OBJC_EMPTY_STATIC_INIT(OrgJodaTimeChronoBasicMonthOfYearDateTimeField)

FOUNDATION_EXPORT void OrgJodaTimeChronoBasicMonthOfYearDateTimeField_initWithOrgJodaTimeChronoBasicChronology_withInt_(OrgJodaTimeChronoBasicMonthOfYearDateTimeField *self, OrgJodaTimeChronoBasicChronology *chronology, jint leapMonth);

FOUNDATION_EXPORT OrgJodaTimeChronoBasicMonthOfYearDateTimeField *new_OrgJodaTimeChronoBasicMonthOfYearDateTimeField_initWithOrgJodaTimeChronoBasicChronology_withInt_(OrgJodaTimeChronoBasicChronology *chronology, jint leapMonth) NS_RETURNS_RETAINED;

FOUNDATION_EXPORT OrgJodaTimeChronoBasicMonthOfYearDateTimeField *create_OrgJodaTimeChronoBasicMonthOfYearDateTimeField_initWithOrgJodaTimeChronoBasicChronology_withInt_(OrgJodaTimeChronoBasicChronology *chronology, jint leapMonth);

J2OBJC_TYPE_LITERAL_HEADER(OrgJodaTimeChronoBasicMonthOfYearDateTimeField)

#endif

#pragma pop_macro("OrgJodaTimeChronoBasicMonthOfYearDateTimeField_INCLUDE_ALL")