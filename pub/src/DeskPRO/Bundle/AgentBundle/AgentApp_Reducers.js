import Application_dp_window                              from "./Modules/Application/Reducers/dp_window.js";
import Application_routing                                from "./Modules/Application/Reducers/routing.js";
import Application_user                                   from "./Modules/Application/Reducers/user.js";
import Tasks_agentList                                    from "./Modules/Tasks/Reducers/agentList.js";
import Tasks_createdProject                               from "./Modules/Tasks/Reducers/createdProject.js";
import Tasks_departmentList                               from "./Modules/Tasks/Reducers/departmentList.js";
import Tasks_labelList                                    from "./Modules/Tasks/Reducers/labelList.js";
import Tasks_projectCreate                                from "./Modules/Tasks/Reducers/projectCreate.js";
import Tasks_projectList                                  from "./Modules/Tasks/Reducers/projectList.js";
import Tasks_taskCreate                                   from "./Modules/Tasks/Reducers/taskCreate.js";
import Tasks_taskFrameList                                from "./Modules/Tasks/Reducers/taskFrameList.js";
import Tasks_taskList                                     from "./Modules/Tasks/Reducers/taskList.js";
import Tasks_teamList                                     from "./Modules/Tasks/Reducers/teamList.js";
import Test_foo_bar                                       from "./Modules/Test/Reducers/foo/bar.js";
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
  "Application": {
    "dp_window":                                          Application_dp_window,
    "routing":                                            Application_routing,
    "user":                                               Application_user,
  },
  "Tasks": {
    "agentList":                                          Tasks_agentList,
    "createdProject":                                     Tasks_createdProject,
    "departmentList":                                     Tasks_departmentList,
    "labelList":                                          Tasks_labelList,
    "projectCreate":                                      Tasks_projectCreate,
    "projectList":                                        Tasks_projectList,
    "taskCreate":                                         Tasks_taskCreate,
    "taskFrameList":                                      Tasks_taskFrameList,
    "taskList":                                           Tasks_taskList,
    "teamList":                                           Tasks_teamList,
  },
  "Test": {
    "foo": {
      "bar":                                              Test_foo_bar,
    },
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
