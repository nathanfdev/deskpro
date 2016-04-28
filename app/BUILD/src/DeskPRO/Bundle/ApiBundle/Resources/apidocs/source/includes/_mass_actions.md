
# Mass Actions API
> Example of the POST request body
 
```json
{
    "ids":[66, 123, 234],
    "params":
      {
        "set_status": "resolved",
        "assign": {"agent": 2},
        "set_product": 5,
        "set_category": 4,
        "set_workflow": 3,
        "set_language": 5,
        "set_followers": [512, 1, 2, 3, 4, 5],
        "reply": {
                   "message":     "<p>Fine HTML or nl2br message</p>", 
                   "isAgentNote": 1
        }
        "set_of_actions":["mark_as_spam", "delete"]
      }
 }
```
For performing mass actions on collection of objects, we must send POST to **/mass_actions/{content}** endpoint. 
See example on javascript tab.

## Feedback actions
```json
{
   "set_category": "Linux",
   "set_hidden_status": "spam",
   "set_status_category": 1,
   "set_type": 2,
   "add_labels": ["label1", "second", "any string"],
   "remove_labels": ["label1", "second", "any string"],
   "set_of_actions":["approve"]
}
``` 
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

## Feedback Comments actions
```json
{
   "set_of_actions":["approve"]
}
```
* Endpoint: **/mass_actions/feedback_comments**
* Available actions:
    * approve
    * delete

## Tasks actions
        
```json
{
   "set_due_date":["2016-04-04T12:03:35+03:00"],
   "set_project": 1,
   "assign": {"agent": 10},
   "set_status": [1],
   "set_of_actions": ["delete"]
}
```
* Endpoint: **/mass_actions/tasks**
* Available actions (see example on javascript tab):
    * set_due_date. Options: dateAsString (string)
    * set_project. Options: projectId (int)
    * set_status. *Available values: 0 (Uncomplete), 1 (Done)*
    * assign: {"agent": personId OR "team": teamId OR "department": departmentId}
    * delete


## Tickets actions     
```json
{
    "set_status": "resolved",
    "assign": {"agent": 2},
    "set_product": 5,
    "set_category": 4,
    "set_workflow": 3,
    "set_language": 5,
    "set_followers": [512, 1, 2, 3, 4, 5],
    "reply": {
               "message":     "<p>Fine HTML or nl2br message</p>", 
               "isAgentNote": 1
    }
    "set_of_actions":["mark_as_spam", "delete", "unassign"]
    "set_of_actions":["mark_as_spam", "delete"]
}
```
> For unassign agent and followers

```json
{
    "assign": {"agent": null},
    "set_followers": [],
}
```

* Endpoint: **/mass_actions/tickets**
* Available actions (see example on javascript tab):
    * set_followers. Options: [array of agentIds] OR [] *Empty array for remove all followers*
    * set_product. Options: productId (int)
    * set_status. Options: statusName (string). *Available values: awaiting_agent, awaiting_user, resolved, archived*
    * set_workflow. Options: workflowId (int)
    * set_category. Options: categoryId (int)
    * assign: {"agent": personId OR "team": teamId OR "department": departmentId}
    * reply: {"message": HTML or plain text, "isAgentNote": 0 | 1}
    * delete
    * mark_as_spam
    * unassign. Set agent and team to null and department to default value


