import React from "react";

export default class TicketsListFrame extends React.Component {
  render() {
    return (
      <section className="ticket-list-frame">
        <div className="ticket-list">
              <div className="tickets-control-bar">

                <div className="bulk-edit-control">
                  <a href="#">
                    <span><i className="fa fa-check"></i></span>
                  </a>
                </div>

                <a href="#" className="ticket-control-button">
                  <span className="title">Order by:</span>
                  <span className="focus">Date</span>
                  <span className="down">Asc <i className="fa fa-caret-down"></i></span>
                </a>

                <a href="#" className="ticket-control-button">
                  <span className="title">Filter by:</span>
                  <span className="focus">12</span>
                  <span className="down">Completed <i className="fa fa-caret-down"></i></span>
                </a>

                <a href="#" className="ticket-control-button">
                  <span className="title">View:</span>
                  <span className="multi">
                    List
                    <span className="multi-down"><i className="fa fa-caret-down"></i></span>
                  </span>
                </a>
              </div>


              <div className="ticket">
                <div className="bulk-editing"></div>

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Count records per hour within a time span</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="org-avatar"></span>
                  <span className="agent-org"> BitDefender,CEO</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-1"><span>1</span></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>

              </div>


              <div className="ticket">
                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Problems using windows 7 built-in virtual wifi hotspot</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Sophie Norton</span>

                  <span className="org-avatar"></span>
                  <span className="agent-org"> BitDefender,CEO</span>

                  <span className="agent-email">&lt;SophieNorton@teleworm.us&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-2"><span>2</span></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">SLA: <span className="sla-yellow">25m</span> <span className="sla-red">4h33m</span> </div>
                </div>
              </div>


              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">JIRA REST API to get work log - "You do not have the permission to see the specified issue"</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Sophie Norton</span>

                  <span className="org-avatar"></span>
                  <span className="agent-org"> BitDefender,CEO</span>

                  <span className="agent-email">&lt;SophieNorton@teleworm.us&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-3"><span>3</span></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags" ><i className="fa fa-flag"></i></span>
                  <div className="ticket-timer"><span className="sla-red">SLA Failed</span> </div>
                </div>
              </div>


              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a href="#">Count records per hour within a time span</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status resolved"><i className="fa fa-check"></i></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>


              <div className="ticket ticket-active">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">How could manage the communication between a WCF thread and another thread?</a>
                  <span className="ticket-label">Bug</span><span className="ticket-label">Update</span>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status on-hold"><i className="fa fa-pause"></i></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>


              <div className="ticket locked">

                <span className="lock"><i className="fa fa-lock"></i></span>
                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Customize auto-configured Spring Boot Bean</a>
                </div>
                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-4"><span>4</span></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>


              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">How could manage the communication between a WCF thread and another thread?</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-extras">
                  <span>Assigned to: <a href="#"><span className="chat-avatar"></span>Shawn Butler</a></span>
                  <span><i className="fa fa-clock-o"></i> Created 25m ago</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-5"><span>5</span></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>

              

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Problems using windows 7 built-in virtual wifi hotspot</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Sophie Norton</span>

                  <span className="org-avatar"></span>
                  <span className="agent-org"> BitDefender,CEO</span>

                  <span className="agent-email">&lt;SophieNorton@teleworm.us&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-6"><span>6</span></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">SLA: <span className="sla-yellow">25m</span> <span className="sla-red">4h33m</span> </div>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">JIRA REST API to get work log - "You do not have the permission to see the specified issue"</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Sophie Norton</span>

                  <span className="org-avatar"></span>
                  <span className="agent-org"> BitDefender,CEO</span>

                  <span className="agent-email">&lt;SophieNorton@teleworm.us&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status awaiting-agent"><i className="fa fa-user"></i></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags" ><i className="fa fa-flag"></i></span>
                  <div className="ticket-timer"><span className="sla-red">SLA Failed</span> </div>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a href="#">Count records per hour within a time span</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-7"><span>7</span></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">How could manage the communication between a WCF thread and another thread?</a>
                  <span className="ticket-label">Bug</span><span className="ticket-label">Update</span>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-8"><span>8</span></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>

              <div className="ticket locked">

                <span className="lock"><i className="fa fa-lock"></i></span>
                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Customize auto-configured Spring Boot Bean</a>
                </div>
                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-9"><span>9</span></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">How could manage the communication between a WCF thread and another thread?</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-extras">
                  <span>Assigned to: <a href="#"><span className="chat-avatar"></span>Shawn Butler</a></span>
                  <span><i className="fa fa-clock-o"></i> Created 25m ago</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status on-hold"><i className="fa fa-pause"></i></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Problems using windows 7 built-in virtual wifi hotspot</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Sophie Norton</span>

                  <span className="org-avatar"></span>
                  <span className="agent-org"> BitDefender,CEO</span>

                  <span className="agent-email">&lt;SophieNorton@teleworm.us&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status level-4"><span>4</span></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">SLA: <span className="sla-yellow">25m</span> <span className="sla-red">4h33m</span> </div>
                </div>
              </div>

              <div className="ticket ticket-active">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">JIRA REST API to get work log - "You do not have the permission to see the specified issue"</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Sophie Norton</span>

                  <span className="org-avatar"></span>
                  <span className="agent-org"> BitDefender,CEO</span>

                  <span className="agent-email">&lt;SophieNorton@teleworm.us&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status awaiting-agent"><i className="fa fa-user"></i></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags" ><i className="fa fa-flag"></i></span>
                  <div className="ticket-timer"><span className="sla-red">SLA Failed</span> </div>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Count records per hour within a time span</a>
                </div>

                <div className="agent">
                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>
                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status resolved"><i className="fa fa-check"></i></span>
                  <span className="assigned-agent" ></span>
                  <span className="ticket-department"><i className="fa fa-users"></i></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>

                <div className="ticket-intro">
                  <p>I want a pop up window to display while another form loads content from a server so the lorel ipsum dolor sit amet can work with.</p>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">How could manage the communication between a WCF thread and another thread?</a>
                  <span className="ticket-label">Bug</span><span className="ticket-label">Update</span>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status on-hold"><i className="fa fa-pause"></i></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>

                <div className="ticket-intro">
                  <p>I'm trying to implement Infinite Scrolling on a gridview to speed up my web application, since the gridview is being bound to a sql query that returns thousands of records at start (it's the client's wish, and I can't change that.)</p>
                </div>
              </div>

              <div className="ticket locked">

                <span className="lock"><i className="fa fa-lock"></i></span>
                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">Customize auto-configured Spring Boot Bean</a>
                </div>
                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status on-hold"><i className="fa fa-pause"></i></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>

                <div className="ticket-intro">
                  <p>I want a pop up window to display while another form loads content from a server so the lorel ipsum dolor sit amet can work with.</p>
                </div>
              </div>

              <div className="ticket">

                <span className="ticket-id">#34528</span>
                <div className="ticket-title">
                  <a onclick="checkTheCheckbox(this); return false;" href="#"><span className="checkbox"><i className="fa fa-check"></i></span></a>
                  <a href="#">How could manage the communication between a WCF thread and another thread?</a>
                </div>

                <div className="agent">

                  <span className="chat-avatar"></span>
                  <span className="agent-name">Nelson Manning</span>

                  <span className="agent-email">&lt;an-excellent-email@example.com&gt;</span>
                </div>

                <div className="ticket-extras">
                  <span>Assigned to: <a href="#"><span className="chat-avatar"></span>Shawn Butler</a></span>
                  <span><i className="fa fa-clock-o"></i> Created 25m ago</span>
                </div>

                <div className="ticket-details">
                  <span className="ticket-status on-hold"><i className="fa fa-pause"></i></span>
                  <span className="assigned-agent"><span>CP</span></span>
                  <span className="ticket-department" ></span>
                  <span className="ticket-flags"></span>
                  <div className="ticket-timer">Last reply: 25m ago</div>
                </div>
              </div>

              

            </div>
      </section>
    );
  }
}
