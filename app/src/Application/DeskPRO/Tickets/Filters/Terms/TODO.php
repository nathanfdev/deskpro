<?php
// Empty implementations of term objects as we build up crud

namespace Application\DeskPRO\Tickets\Filters\Terms;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketChangelog;

class FilterEmailAccount                        extends AbstractFilterTerm { }
class FilterEmailSubject                        extends AbstractFilterTerm { }
class FilterEmailBody                           extends AbstractFilterTerm { }
class FilterEmailToName                         extends AbstractFilterTerm { }
class FilterEmailToAddress                      extends AbstractFilterTerm { }
class FilterEmailFromName                       extends AbstractFilterTerm { }
class FilterEmailFromAddress                    extends AbstractFilterTerm { }
class FilterEmailCcName                         extends AbstractFilterTerm { }
class FilterEmailCcAddress                      extends AbstractFilterTerm { }
class FilterEmailHeader                         extends AbstractFilterTerm { }
class FilterDepartment                          extends AbstractFilterTerm { }
class FilterProduct                             extends AbstractFilterTerm { }
class FilterCategory                            extends AbstractFilterTerm { }
class FilterPriority                            extends AbstractFilterTerm { }
class FilterWorkflow                            extends AbstractFilterTerm { }
class FilterSubject                             extends AbstractFilterTerm { }
class FilterMessage                             extends AbstractFilterTerm { }
class FilterHasAttach                           extends AbstractFilterTerm { }
class FilterHasAttachType                       extends AbstractFilterTerm { }
class FilterHasAttachName                       extends AbstractFilterTerm { }
class FilterUserName                            extends AbstractFilterTerm { }
class FilterUserEmailAddress                    extends AbstractFilterTerm { }
class FilterUserLabels                          extends AbstractFilterTerm { }
class FilterUserUsergroups                      extends AbstractFilterTerm { }
class FilterUserLanguage                        extends AbstractFilterTerm { }
class FilterUserIsManager                       extends AbstractFilterTerm { }
class FilterPersonIsDisabled                    extends AbstractFilterTerm { }
class FilterOrgName                             extends AbstractFilterTerm { }
class FilterOrgLabels                           extends AbstractFilterTerm { }
class FilterOrgEmailDomain                      extends AbstractFilterTerm { }
class FilterOrgUsergroups                       extends AbstractFilterTerm { }
class FilterDayOfWeek                           extends AbstractFilterTerm { }
class FilterTimeOfDay                           extends AbstractFilterTerm { }
class FilterWorkingHours                        extends AbstractFilterTerm { }