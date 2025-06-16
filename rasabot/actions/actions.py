from rasa_sdk import Action

class ActionSearchProducts(Action):
    def name(self):
        return "action_search_products"

    def run(self, dispatcher, tracker, domain):
        return []  # All logic is now handled in PHP