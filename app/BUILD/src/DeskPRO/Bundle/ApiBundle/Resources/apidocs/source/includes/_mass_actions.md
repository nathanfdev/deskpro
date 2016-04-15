
# Mass Actions API
For performing mass actions on collection of objects, we must send POST to **/mass_actions/{content}** endpoint. 
See example on javascript tab.

> Example of the POST reguest body
 
```javascript
{
    ids:[66, 123, 234],      // List of IDs of object for actions applying
    params:                 // Actions parameters
      {
        set_status: "resolved",
        assign: {agent: 2},
        set_product: 5,
        set_category: 4,
        set_workflow: 3,
        set_language: 5,
        set_followers: [512, 1, 2, 3, 4, 5],
        set_of_actions":["mark_as_spam", "delete"]
      }
 }
```

## Feedback actions
* Endpoint: **/mass_actions/feedback**
* Available actions (see example on javascript tab):
    * set_category. Options: categoryName (string). *Legacy stuff: category of Feedback is CustomDataFeedback entity*
    * set_hidden_status. Options: hiddenStatusName (string). *Available values: deleted, draft, spam and unpublished*
    * set_status_category. Options: statusCategoryId (int)
    * set_type. Options: typeId (int). *Legacy stuff: type of Feedback is FeedbackCategory entity*
    * add_labels. Options: ["label1", "second", "any string"]
    * remove_labels. Options: ["label1", "second", "any string"]
    * approve
    * delete
        
```javascript
{
   set_category: "Linux",
   set_hidden_status: "spam",
   set_status_category: 1,
   set_type: 2
   add_labels: ["label1", "second", "any string"]
   remove_labels: ["label1", "second", "any string"]
   set_of_actions:["approve"]
}
```        

## Feedback Comments actions
* Endpoint: **/mass_actions/feedback_comments**
* Available actions:
    * approve
    * delete
        
```javascript
{
   set_of_actions:["approve"]
}
```

## Tasks actions
* Endpoint: **/mass_actions/tasks**
* Available actions (see example on javascript tab):
    * set_due_date. Options: dateAsString (string)
    * set_project. Options: projectId (int)
    * set_status. *Available values: 0 (Uncomplete), 1 (Done)*
    * assign: {"agent": personId OR "team": teamId OR "department": departmentId}
    * delete
        
```javascript
{
   set_due_date:["2016-04-04T12:03:35+03:00"],
   set_project: 1,
   assign: {"agent": 10},
   set_status: [1],
   set_of_actions: ["delete"]
}
```

## Tickets actions
* Endpoint: **/mass_actions/tickets**
* Available actions (see example on javascript tab):
    * set_followers. Options: [array of agentIds]
    * set_product. Options: productId (int)
    * set_status. Options: statusName (string). *Available values: awaiting_agent, awaiting_user, resolved, archived*
    * set_workflow. Options: workflowId (int)
    * set_category. Options: categoryId (int)
    * assign: {"agent": personId OR "team": teamId OR "department": departmentId}
    * delete
    * mark_as_spam
        
```javascript
{
    set_status: "resolved",
    assign: {agent: 2},
    set_product: 5,
    set_category: 4,
    set_workflow: 3,
    set_language: 5,
    set_followers: [512, 1, 2, 3, 4, 5],
    set_of_actions":["mark_as_spam", "delete"]
}
```

