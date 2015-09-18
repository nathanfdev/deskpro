import ROOT_dp_window                                     from "./Modules/Application/Reducers/dp_window.js";
import ROOT_routing                                       from "./Modules/Application/Reducers/routing.js";
import ROOT_user                                          from "./Modules/Application/Reducers/user.js";
import ROOT_CrmNav                                        from "./Modules/CRM/Reducers/CrmNav.js";
import Chat_list                                          from "./Modules/Chat/Reducers/list.js";
import Chat_nav                                           from "./Modules/Chat/Reducers/nav.js";
import Common_departments                                 from "./Modules/Common/Reducers/departments.js";
import Common_users                                       from "./Modules/Common/Reducers/users.js";
import ROOT_FeedbackList                                  from "./Modules/Feedback/Reducers/FeedbackList.js";
import IM_list                                            from "./Modules/IM/Reducers/list.js";
import ROOT_PublishList                                   from "./Modules/Publish/Reducers/PublishList.js";
import ROOT_PublishNav                                    from "./Modules/Publish/Reducers/PublishNav.js";
import ROOT_agentList                                     from "./Modules/Tasks/Reducers/agentList.js";
import ROOT_createdProject                                from "./Modules/Tasks/Reducers/createdProject.js";
import ROOT_departmentList                                from "./Modules/Tasks/Reducers/departmentList.js";
import ROOT_labelList                                     from "./Modules/Tasks/Reducers/labelList.js";
import ROOT_projectCreate                                 from "./Modules/Tasks/Reducers/projectCreate.js";
import ROOT_projectList                                   from "./Modules/Tasks/Reducers/projectList.js";
import ROOT_taskCreate                                    from "./Modules/Tasks/Reducers/taskCreate.js";
import ROOT_taskFilter                                    from "./Modules/Tasks/Reducers/taskFilter.js";
import ROOT_taskFrameList                                 from "./Modules/Tasks/Reducers/taskFrameList.js";
import ROOT_taskList                                      from "./Modules/Tasks/Reducers/taskList.js";
import ROOT_taskListList                                  from "./Modules/Tasks/Reducers/taskListList.js";
import ROOT_teamList                                      from "./Modules/Tasks/Reducers/teamList.js";
import Test_test                                          from "./Modules/Test/Reducers/test.js";
import Tickets_AgentTeams                                 from "./Modules/Tickets/Reducers/AgentTeams.js";
import Tickets_FilterSetFilterGroups                      from "./Modules/Tickets/Reducers/FilterSetFilterGroups.js";
import Tickets_FilterSetFiltersList                       from "./Modules/Tickets/Reducers/FilterSetFiltersList.js";
import Tickets_FilterSetsCounts                           from "./Modules/Tickets/Reducers/FilterSetsCounts.js";
import Tickets_FilterSetsList                             from "./Modules/Tickets/Reducers/FilterSetsList.js";
import Tickets_LabelsList                                 from "./Modules/Tickets/Reducers/LabelsList.js";
import Tickets_SidebarHover                               from "./Modules/Tickets/Reducers/SidebarHover.js";
import Tickets_StarsCounts                                from "./Modules/Tickets/Reducers/StarsCounts.js";
import Tickets_TicketsList                                from "./Modules/Tickets/Reducers/TicketsList.js";
import Tickets_Translations                               from "./Modules/Tickets/Reducers/Translations.js";
import Tickets_departments                                from "./Modules/Tickets/Reducers/departments.js";
import Tickets_people                                     from "./Modules/Tickets/Reducers/people.js";

export default {
  "dp_window":                                            ROOT_dp_window,
  "routing":                                              ROOT_routing,
  "user":                                                 ROOT_user,
  "CrmNav":                                               ROOT_CrmNav,
  "FeedbackList":                                         ROOT_FeedbackList,
  "PublishList":                                          ROOT_PublishList,
  "PublishNav":                                           ROOT_PublishNav,
  "agentList":                                            ROOT_agentList,
  "createdProject":                                       ROOT_createdProject,
  "departmentList":                                       ROOT_departmentList,
  "labelList":                                            ROOT_labelList,
  "projectCreate":                                        ROOT_projectCreate,
  "projectList":                                          ROOT_projectList,
  "taskCreate":                                           ROOT_taskCreate,
  "taskFilter":                                           ROOT_taskFilter,
  "taskFrameList":                                        ROOT_taskFrameList,
  "taskList":                                             ROOT_taskList,
  "taskListList":                                         ROOT_taskListList,
  "teamList":                                             ROOT_teamList,
  "Chat": {
    "list":                                               Chat_list,
    "nav":                                                Chat_nav,
  },
  "Common": {
    "departments":                                        Common_departments,
    "users":                                              Common_users,
  },
  "IM": {
    "list":                                               IM_list,
  },
  "Test": {
    "test":                                               Test_test,
  },
  "Tickets": {
    "AgentTeams":                                         Tickets_AgentTeams,
    "FilterSetFilterGroups":                              Tickets_FilterSetFilterGroups,
    "FilterSetFiltersList":                               Tickets_FilterSetFiltersList,
    "FilterSetsCounts":                                   Tickets_FilterSetsCounts,
    "FilterSetsList":                                     Tickets_FilterSetsList,
    "LabelsList":                                         Tickets_LabelsList,
    "SidebarHover":                                       Tickets_SidebarHover,
    "StarsCounts":                                        Tickets_StarsCounts,
    "TicketsList":                                        Tickets_TicketsList,
    "Translations":                                       Tickets_Translations,
    "departments":                                        Tickets_departments,
    "people":                                             Tickets_people,
  },
};
